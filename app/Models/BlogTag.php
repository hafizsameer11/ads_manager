<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Get the blogs that have this tag.
     */
    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_tag', 'tag_id', 'blog_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
                // Ensure uniqueness
                $originalSlug = $tag->slug;
                $counter = 1;
                while (static::where('slug', $tag->slug)->exists()) {
                    $tag->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
                // Ensure uniqueness
                $originalSlug = $tag->slug;
                $counter = 1;
                while (static::where('slug', $tag->slug)->where('id', '!=', $tag->id)->exists()) {
                    $tag->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }
}
