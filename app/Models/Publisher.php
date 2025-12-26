<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publisher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_earnings',
        'pending_balance',
        'paid_balance',
        'minimum_payout',
        'status',
        'tier',
        'is_premium',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'paid_balance' => 'decimal:2',
        'minimum_payout' => 'decimal:2',
        'is_premium' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the publisher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the websites for the publisher.
     */
    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    /**
     * Get the withdrawals for the publisher.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Get available balance for withdrawal.
     */
    public function getAvailableBalanceAttribute(): float
    {
        return max(0, $this->balance - $this->pending_balance);
    }

    /**
     * Check if publisher can withdraw.
     */
    public function canWithdraw(): bool
    {
        return $this->balance >= $this->minimum_payout && $this->status === 'approved';
    }
}