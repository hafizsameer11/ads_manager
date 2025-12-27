<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbuseReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'url',
        'email',
        'description',
        'ip_address',
        'user_agent',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Mark as reviewed.
     */
    public function markAsReviewed(?string $notes = null): void
    {
        $this->update([
            'status' => 'reviewed',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Mark as resolved.
     */
    public function markAsResolved(?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Mark as dismissed.
     */
    public function markAsDismissed(?string $notes = null): void
    {
        $this->update([
            'status' => 'dismissed',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
        ]);
    }
}
