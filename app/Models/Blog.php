<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'featured_image',
        'short_description',
        'content',
        'category_id',
        'author_id',
        'status',
        'meta_title',
        'meta_description',
        'views_count',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    /**
     * Get the category that owns the blog.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Get the author of the blog.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the tags for the blog.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_tag', 'blog_id', 'tag_id');
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }
        return null;
    }

    /**
     * Scope to get published blogs.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope to get draft blogs.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by tag.
     */
    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('blog_tags.id', $tagId);
        });
    }

    /**
     * Increment views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
                // Ensure uniqueness
                $originalSlug = $blog->slug;
                $counter = 1;
                while (static::where('slug', $blog->slug)->exists()) {
                    $blog->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Set published_at if status is published
            if ($blog->status === 'published' && empty($blog->published_at)) {
                $blog->published_at = now();
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
                // Ensure uniqueness
                $originalSlug = $blog->slug;
                $counter = 1;
                while (static::where('slug', $blog->slug)->where('id', '!=', $blog->id)->exists()) {
                    $blog->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            // Set published_at if status is changing to published
            if ($blog->status === 'published' && empty($blog->published_at)) {
                $blog->published_at = now();
            }
        });

        static::deleting(function ($blog) {
            // Delete featured image if exists
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
        });
    }
}
