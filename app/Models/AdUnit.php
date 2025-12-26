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
            
            return "<iframe src=\"{$baseUrl}/api/ad/{$this->unit_code}\" width=\"{$width}\" height=\"{$height}\" frameborder=\"0\"></iframe>";
        } else {
            // Popup
            return "<script src=\"{$baseUrl}/api/ad/{$this->unit_code}?type=popup\"></script>";
        }
    }
}