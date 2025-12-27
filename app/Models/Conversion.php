<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'click_id',
        'impression_id',
        'ad_unit_id',
        'website_id',
        'conversion_type',
        'conversion_value',
        'conversion_id',
        'ip_address',
        'user_agent',
        'country_code',
        'conversion_data',
        'postback_url',
        'postback_sent',
        'converted_at',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'conversion_data' => 'array',
        'postback_sent' => 'boolean',
        'converted_at' => 'datetime',
    ];

    /**
     * Get the campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the click.
     */
    public function click(): BelongsTo
    {
        return $this->belongsTo(Click::class);
    }

    /**
     * Get the impression.
     */
    public function impression(): BelongsTo
    {
        return $this->belongsTo(Impression::class);
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
     * Generate unique conversion ID.
     */
    public static function generateConversionId(): string
    {
        return 'conv_' . time() . '_' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
    }
}
