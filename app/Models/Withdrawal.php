<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'publisher_id',
        'amount',
        'payment_method',
        'payment_details',
        'status',
        'processed_at',
        'rejection_reason',
        'notes',
        'manual_payment_account_id',
        'account_type',
        'account_name',
        'account_number',
        'payment_screenshot',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the publisher that owns the withdrawal.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * Get the manual payment account.
     */
    public function manualPaymentAccount(): BelongsTo
    {
        return $this->belongsTo(ManualPaymentAccount::class);
    }

    /**
     * Get the payment screenshot URL.
     */
    public function getScreenshotUrlAttribute()
    {
        if ($this->payment_screenshot) {
            return asset('storage/' . $this->payment_screenshot);
        }
        return null;
    }

    /**
     * Approve withdrawal.
     *
     * @return bool
     */
    public function approve(): bool
    {
        return $this->update([
            'status' => 'approved',
            'processed_at' => now(),
        ]);
    }

    /**
     * Reject withdrawal.
     *
     * @param  string  $reason
     * @return bool
     */
    public function reject(string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark withdrawal as processing.
     *
     * @return bool
     */
    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => 'processing',
        ]);
    }

    /**
     * Scope to get pending withdrawals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved withdrawals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
