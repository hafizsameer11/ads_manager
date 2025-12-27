<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id',
        'name',
        'ad_type',
        'pricing_model',
        'budget',
        'daily_budget',
        'bid_amount',
        'target_url',
        'ad_content',
        'start_date',
        'end_date',
        'status',
        'approval_status',
        'rejection_reason',
        'total_spent',
        'impressions',
        'clicks',
        'ctr',
        'max_impressions_per_user',
        'max_clicks_per_user',
        'rotation_weight',
        'approved_at',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'daily_budget' => 'decimal:2',
        'bid_amount' => 'decimal:4',
        'total_spent' => 'decimal:2',
        'ctr' => 'decimal:2',
        'ad_content' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'max_impressions_per_user' => 'integer',
        'max_clicks_per_user' => 'integer',
        'rotation_weight' => 'integer',
    ];

    /**
     * Get the advertiser that owns the campaign.
     */
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(Advertiser::class);
    }

    /**
     * Get the targeting settings for the campaign.
     */
    public function targeting(): HasOne
    {
        return $this->hasOne(CampaignTargeting::class);
    }

    /**
     * Get the impressions for the campaign.
     */
    public function campaignImpressions(): HasMany
    {
        return $this->hasMany(Impression::class);
    }

    /**
     * Get the clicks for the campaign.
     */
    public function campaignClicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Get the conversions for the campaign.
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(Conversion::class);
    }

    /**
     * Calculate CTR.
     */
    public function calculateCTR(): float
    {
        if ($this->impressions == 0) {
            return 0;
        }
        return ($this->clicks / $this->impressions) * 100;
    }

    /**
     * Check if campaign is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->approval_status === 'approved' &&
               $this->budget > $this->total_spent;
    }
}