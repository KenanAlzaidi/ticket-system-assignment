<?php

namespace App\Contracts;

use App\Models\Ticket;
use Illuminate\Support\Collection;

/**
 * Interface TicketRepositoryInterface
 *
 * Defines the contract for ticket data access.
 * @package App\Contracts
 */
interface TicketRepositoryInterface
{
    /**
     * Create a new ticket.
     *
     * @param array $data Associative array of ticket attributes.
     * @return Ticket The created ticket instance.
     */
    public function create(array $data): Ticket;

    /**
     * Find a specific ticket by ID.
     *
     * @param int $id The ticket ID.
     * @return Ticket|null The ticket instance or null if not found.
     */
    public function findById(int $id): ?Ticket;

    /**
     * Get a query builder that unions all department databases.
     *
     * This is used for Server-Side DataTables to allow pagination and sorting
     * across multiple decentralized databases that reside within the same server.
     *
     * @param string|null $departmentFilter Optional department name to filter by.
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getCrossDatabaseQuery(?string $departmentFilter = null);

    /**
     * Update a ticket with a note and new status.
     *
     * @param int $id The ticket ID
     * @param string $note The admin note content
     * @param string $status The new status for the ticket
     * @return Ticket
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateWithNote(int $id, string $note, string $status = 'noted'): Ticket;
}
