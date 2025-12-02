<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AdminNote
 * 
 * Represents an internal note added by an admin to a specific ticket.
 * Stored in the same department database as the ticket it belongs to.
 * 
 * @property int $id
 * @property int $ticket_id
 * @property string $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * 
 * @property-read Ticket $ticket
 * 
 * @package App\Models
 */
class AdminNote extends Model
{
    protected $fillable = ['ticket_id', 'note'];

    /**
     * Get the ticket that owns the note.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
