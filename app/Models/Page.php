<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * Get the user who created this page.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this page.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->where('is_published', true);
    }

    /**
     * Get page by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::published()->where('slug', $slug)->first();
    }
}
