<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Impression extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'ad_unit_id',
        'website_id',
        'ip_address',
        'user_agent',
        'country_code',
        'device_type',
        'os',
        'browser',
        'is_bot',
        'is_vpn',
        'is_proxy',
        'revenue',
        'publisher_earning',
        'admin_profit',
        'impression_at',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'is_vpn' => 'boolean',
        'is_proxy' => 'boolean',
        'revenue' => 'decimal:4',
        'publisher_earning' => 'decimal:4',
        'admin_profit' => 'decimal:4',
        'impression_at' => 'datetime',
    ];

    /**
     * Get the campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the ad unit.
     */
    public function adUnit(): BelongsTo
    {
        return $this->belongsTo(AdUnit::class);
    }

    /**
     * Get the website.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the click from this impression.
     */
    public function click(): HasOne
    {
        return $this->hasOne(Click::class);
    }
}