<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'referred_type',
        'status',
        'commission_rate',
        'total_earnings',
        'paid_earnings',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'paid_earnings' => 'decimal:2',
    ];

    /**
     * Get the user who referred (referrer).
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Get the referred user.
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    /**
     * Calculate earnings for this referral.
     *
     * @param  float  $amount
     * @return float
     */
    public function calculateEarnings(float $amount): float
    {
        return round($amount * ($this->commission_rate / 100), 2);
    }

    /**
     * Add earnings to referral.
     *
     * @param  float  $amount
     * @return void
     */
    public function addEarnings(float $amount): void
    {
        $earning = $this->calculateEarnings($amount);
        $this->increment('total_earnings', $earning);
    }

    /**
     * Mark earnings as paid.
     *
     * @param  float  $amount
     * @return void
     */
    public function markAsPaid(float $amount): void
    {
        $this->increment('paid_earnings', $amount);
    }
}
