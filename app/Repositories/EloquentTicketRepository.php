<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Contracts\TicketRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Class EloquentTicketRepository
 *
 * Implementation of TicketRepositoryInterface using Laravel's Eloquent ORM.
 * This repository handles database interactions for Ticket model across multiple
 * department databases by utilizing the injected model instance which has
 * a dynamically set connection.
 *
 * @package App\Repositories
 */
class EloquentTicketRepository implements TicketRepositoryInterface
{
    /**
     * The Ticket model instance.
     *
     * @var Ticket
     */
    protected Ticket $model;

    /**
     * EloquentTicketRepository constructor.
     *
     * @param Ticket $model The model instance, potentially with a dynamic connection.
     */
    public function __construct(Ticket $model)
    {
        $this->model = $model;
    }

    /**
     * Get the underlying model instance.
     * Used by the Factory to set dynamic connections.
     *
     * @return Ticket
     */
    public function getModel(): Ticket
    {
        return $this->model;
    }

    /**
     * Create a new ticket in the database.
     *
     * @param array $data
     * @return Ticket
     */
    public function create(array $data): Ticket
    {
        return $this->model->create($data);
    }

    /**
     * Retrieve a specific ticket by ID.
     *
     * Note: We use find() instead of findOrFail() to allow the controller
     * to handle the 404 logic (or abort) gracefully.
     *
     * @param int $id
     * @return Ticket|null
     */
    public function findById(int $id): ?Ticket
    {
        return $this->model->find($id);
    }

    /**
     * Build a Union query across all department databases.
     *
     * @param string|null $departmentFilter
     * @return \Illuminate\Database\Query\Builder
     */
    public function getCrossDatabaseQuery(?string $departmentFilter = null)
    {
        $departments = config('departments.connection_map');

        // Optimization: If a specific department is requested, query ONLY that connection directly.
        if ($departmentFilter && isset($departments[$departmentFilter])) {
            $connectionName = $departments[$departmentFilter];

            // Return a builder that selects literal 'department name' to match the Union structure
            return Ticket::on($connectionName)
                ->select([
                    'tickets.id',
                    'tickets.subject',
                    'tickets.customer_name',
                    'tickets.customer_email',
                    'tickets.customer_phone',
                    'tickets.status',
                    'tickets.updated_at',
                    DB::raw("? as department")
                ])
                ->addBinding($departmentFilter, 'select')
                ->toBase(); // Use raw query builder for consistency with Union return type
        }

        // Otherwise, build the Cross-Database Union
        $queries = [];

        foreach ($departments as $type => $connectionName) {
            $connection = DB::connection($connectionName);
            $dbName = $connection->getDatabaseName();

            // Create a query for this specific database
            // We fully qualify the table name to ensure the UNION executes correctly across contexts
            $query = $connection
                ->table($dbName . '.tickets as tickets')
                ->select([
                    'tickets.id',
                    'tickets.subject',
                    'tickets.customer_name',
                    'tickets.customer_email',
                    'tickets.customer_phone',
                    'tickets.status',
                    'tickets.updated_at',
                    DB::raw('? as department')
                ])
                ->addBinding($type, 'select');

            $queries[] = $query;
        }

        // Combine all queries using UNION ALL
        $firstQuery = array_shift($queries);

        // Handle edge case where only 1 department exists
        if (empty($queries)) {
             return $firstQuery->getConnection()->query()->fromSub($firstQuery, 'all_tickets');
        }

        foreach ($queries as $query) {
            $firstQuery->unionAll($query);
        }

        // CRITICAL FIX: Wrap the UNION in a subquery using the SAME connection as the first query.
        // Using DB::query() defaults to the 'mysql' connection, which might fail if it tries
        // to inspect the subquery schema or runs count() against the wrong context.
        // By using $firstQuery->getConnection(), we ensure the wrapper executes on a valid department DB.
        return $firstQuery->getConnection()->query()->fromSub($firstQuery, 'all_tickets');
    }

    /**
     * Update a ticket with a note and new status.
     *
     * @param int $id
     * @param string $note
     * @param string $status
     * @return Ticket
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateWithNote(int $id, string $note, string $status = 'noted'): Ticket
    {
        return DB::transaction(function () use ($id, $note, $status) {
            $ticket = $this->model->findOrFail($id);

            $ticket->notes()->create([
                'note' => $note
            ]);

            $ticket->status = $status;
            $ticket->save();

            return $ticket;
        });
    }
}
