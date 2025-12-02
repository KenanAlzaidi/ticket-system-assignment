<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Ticket
 *
 * Represents a support ticket in the system.
 *
 * This model is unique because it is designed to operate across multiple database connections.
 * Its connection is set dynamically at runtime by the TicketRepositoryFactory based on the
 * department the ticket belongs to.
 *
 * @property int $id
 * @property string $customer_name
 * @property string $customer_email
 * @property string|null $customer_phone
 * @property string $subject
 * @property string $message
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|AdminNote[] $notes
 *
 * @package App\Models
 */
class Ticket extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'subject',
        'message',
        'status',
    ];

    /**
     * Get the admin notes associated with the ticket.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(AdminNote::class)->latest();
    }

    /**
     * Set the database connection for this model instance dynamically.
     *
     * This wrapper method allows the EloquentConnectionFactory to inject the correct
     * department connection string (e.g., 'technical_issues_department')
     * before the model processes any queries.
     *
     * @param string $connection The name of the database connection from config/database.php
     * @return $this
     */
    public function setDynamicConnection(string $connection)
    {
        $this->setConnection($connection);
        return $this;
    }
}
