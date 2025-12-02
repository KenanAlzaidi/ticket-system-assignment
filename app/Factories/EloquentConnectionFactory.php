<?php

namespace App\Factories;

use App\Repositories\EloquentTicketRepository;
use InvalidArgumentException;

/**
 * Class EloquentConnectionFactory
 *
 * Responsible for creating configured instances of the TicketRepository
 * with the correct database connection for a specific department.
 *
 * This factory abstracts the complexity of dynamic connection switching,
 * ensuring that the Controller only receives a ready-to-use repository.
 *
 * @package App\Factories
 */
class EloquentConnectionFactory
{
    /**
     * The repository instance to configure.
     *
     * @var EloquentTicketRepository
     */
    protected EloquentTicketRepository $repository;

    /**
     * EloquentConnectionFactory constructor.
     *
     * @param EloquentTicketRepository $repository Injected via DI (singleton or transient).
     */
    public function __construct(EloquentTicketRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Creates and configures a TicketRepository based on the provided ticket type.
     *
     * @param string $ticketType The department name (e.g., 'Technical Issues').
     * @return EloquentTicketRepository The repository configured with the correct DB connection.
     * @throws InvalidArgumentException If the ticket type does not map to a valid connection.
     */
    public function make(string $ticketType): EloquentTicketRepository
    {
        $connectionMap = config('departments.connection_map');

        if (!array_key_exists($ticketType, $connectionMap)) {
            throw new InvalidArgumentException("Invalid ticket type: '{$ticketType}'. No corresponding database connection found.");
        }

        $connectionName = $connectionMap[$ticketType];

        // CRITICAL STEP: Set the connection dynamically on the Repository's Model
        // This mutates the model instance within the repository to point to the correct department DB.
        $this->repository->getModel()->setDynamicConnection($connectionName);

        // Return the fully configured Repository instance
        return $this->repository;
    }
}
