<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Generate unique ticket number.
     */
    public static function generateTicketNumber(): string
    {
        do {
            $number = 'TKT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('ticket_number', $number)->exists());

        return $number;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    /**
     * Get the user who created this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to this ticket.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all replies for this ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'ticket_id');
    }

    /**
     * Get public replies (non-internal).
     */
    public function publicReplies(): HasMany
    {
        return $this->replies()->where('is_internal', false);
    }

    /**
     * Get internal replies (admin notes).
     */
    public function internalReplies(): HasMany
    {
        return $this->replies()->where('is_internal', true);
    }

    /**
     * Mark ticket as resolved.
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark ticket as closed.
     */
    public function markAsClosed(): void
    {
        $this->update([
            'status' => 'closed',
        ]);
    }
}
