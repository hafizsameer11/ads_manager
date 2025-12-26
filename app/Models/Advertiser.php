<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertiser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_spent',
        'status',
        'payment_email',
        'payment_info',
        'notes',
        'approved_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'payment_info' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the advertiser.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the campaigns for the advertiser.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Check if advertiser can create campaign.
     */
    public function canCreateCampaign(float $budget): bool
    {
        return $this->balance >= $budget && $this->status === 'approved';
    }
}