<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTargeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'countries',
        'devices',
        'operating_systems',
        'browsers',
        'languages',
        'is_vpn_allowed',
        'is_proxy_allowed',
    ];

    protected $casts = [
        'countries' => 'array',
        'devices' => 'array',
        'operating_systems' => 'array',
        'browsers' => 'array',
        'languages' => 'array',
        'is_vpn_allowed' => 'boolean',
        'is_proxy_allowed' => 'boolean',
    ];

    /**
     * Get the campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}