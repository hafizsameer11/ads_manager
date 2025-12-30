<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transactionable_type',
        'transactionable_id',
        'type',
        'status',
        'amount',
        'transaction_id',
        'payment_method',
        'payment_details',
        'payment_screenshot',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the parent transactionable model (advertiser or publisher).
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark transaction as completed.
     *
     * @return bool
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed.
     *
     * @param  string|null  $notes
     * @return bool
     */
    public function markAsFailed(?string $notes = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'notes' => $notes,
        ]);
    }

    /**
     * Scope to get pending transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get completed transactions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get the payment screenshot URL.
     */
    public function getScreenshotUrlAttribute()
    {
        if ($this->payment_screenshot) {
            return Storage::url($this->payment_screenshot);
        }
        return null;
    }
}
