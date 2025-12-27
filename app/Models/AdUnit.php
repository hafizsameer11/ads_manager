<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AdUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'publisher_id',
        'website_id',
        'name',
        'type',
        'size',
        'frequency',
        'unit_code',
        'width',
        'height',
        'status',
        'is_anti_adblock',
        'cpm_rate',
        'cpc_rate',
    ];

    protected $casts = [
        'is_anti_adblock' => 'boolean',
        'cpm_rate' => 'decimal:4',
        'cpc_rate' => 'decimal:4',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adUnit) {
            if (empty($adUnit->unit_code)) {
                $adUnit->unit_code = self::generateUnitCode();
            }
        });
    }

    /**
     * Get the publisher that owns the ad unit.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    /**
     * Get the website that owns the ad unit.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the impressions for the ad unit.
     */
    public function impressions(): HasMany
    {
        return $this->hasMany(Impression::class);
    }

    /**
     * Get the clicks for the ad unit.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Generate unique unit code.
     */
    public static function generateUnitCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
        } while (self::where('unit_code', $code)->exists());

        return $code;
    }

    /**
     * Get embed code for the ad unit.
     */
    public function getEmbedCodeAttribute(): string
    {
        $baseUrl = config('app.url');
        $unitCode = $this->unit_code;
        
        if ($this->type === 'banner') {
            // Parse size for width and height
            $width = $this->width ?? 300;
            $height = $this->height ?? 250;
            
            if ($this->size) {
                $sizeParts = explode('x', $this->size);
                if (count($sizeParts) === 2) {
                    $width = (int)trim($sizeParts[0]);
                    $height = (int)trim($sizeParts[1]);
                }
            }
            
            // Modern JavaScript SDK approach
            // The SDK automatically detects API URL from script source - no configuration needed!
            return <<<HTML
<!-- Ads Network Ad Unit: {$this->name} -->
<div id="ads-network-{$unitCode}" style="width: {$width}px; height: {$height}px; margin: 0 auto;"></div>
<script>
(function() {
    // Load SDK if not already loaded
    // SDK will auto-detect API URL from script source - no configuration needed!
    if (!window.AdsNetwork) {
        var script = document.createElement('script');
        script.src = '{$baseUrl}/js/ads-network.js';
        script.async = true;
        document.head.appendChild(script);
        script.onload = function() {
            if (window.AdsNetwork) {
                window.AdsNetwork.init('{$unitCode}', '#ads-network-{$unitCode}', {type: 'banner'});
            }
        };
    } else {
        window.AdsNetwork.init('{$unitCode}', '#ads-network-{$unitCode}', {type: 'banner'});
    }
})();
</script>
HTML;
        } else if ($this->type === 'popup') {
            $frequency = $this->frequency ?? 30;
            
            return <<<HTML
<!-- Ads Network Popup Ad: {$this->name} -->
<script>
(function() {
    // Load SDK if not already loaded
    if (!window.AdsNetwork) {
        var script = document.createElement('script');
        script.src = '{$baseUrl}/js/ads-network.js';
        script.async = true;
        document.head.appendChild(script);
        script.onload = function() {
            if (window.AdsNetwork) {
                window.AdsNetwork.init('{$unitCode}', null, {type: 'popup', frequency: {$frequency}});
            }
        };
    } else {
        window.AdsNetwork.init('{$unitCode}', null, {type: 'popup', frequency: {$frequency}});
    }
})();
</script>
HTML;
        } else if ($this->type === 'popunder') {
            $frequency = $this->frequency ?? 30;
            
            return <<<HTML
<!-- Ads Network Popunder Ad: {$this->name} -->
<script>
(function() {
    // Load SDK if not already loaded
    if (!window.AdsNetwork) {
        var script = document.createElement('script');
        script.src = '{$baseUrl}/js/ads-network.js';
        script.async = true;
        document.head.appendChild(script);
        script.onload = function() {
            if (window.AdsNetwork) {
                window.AdsNetwork.init('{$unitCode}', null, {type: 'popunder', frequency: {$frequency}});
            }
        };
        } else {
        window.AdsNetwork.init('{$unitCode}', null, {type: 'popunder', frequency: {$frequency}});
    }
})();
</script>
HTML;
        }
        
        return '';
    }
}