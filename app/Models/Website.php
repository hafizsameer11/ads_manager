<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Website extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When website status changes to rejected or disabled, automatically disable all ad units
        static::updating(function ($website) {
            if ($website->isDirty('status')) {
                $oldStatus = $website->getOriginal('status');
                $newStatus = $website->status;
                
                // If website is being rejected or disabled, pause all ad units
                if (in_array($newStatus, ['rejected', 'disabled']) && !in_array($oldStatus, ['rejected', 'disabled'])) {
                    $website->adUnits()->update(['status' => 'paused']);
                }
            }
        });
    }

    protected $fillable = [
        'publisher_id',
        'domain',
        'name',
        'verification_method',
        'verification_code',
        'verification_status',
        'status',
        'rejection_reason',
        'verified_at',
        'approved_at',
        'rejected_at',
        'admin_note',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the publisher that owns the website.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Publisher::class);
    }

    /**
     * Get the ad units for the website.
     */
    public function adUnits(): HasMany
    {
        return $this->hasMany(AdUnit::class);
    }

    /**
     * Generate verification code.
     */
    public static function generateVerificationCode(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
    }

    /**
     * Check if website is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if website can have ad units.
     */
    public function canHaveAdUnits(): bool
    {
        return $this->status === 'approved';
    }
}