<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogsController extends Controller
{
    /**
     * Display a listing of blogs.
     */
    public function index(Request $request)
    {
        $query = Blog::with(['author', 'category', 'tags'])->latest();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('status', 'published');
            } elseif ($request->status === 'draft') {
                $query->where('status', 'draft');
            }
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $blogs = $query->paginate(20);
        $categories = BlogCategory::active()->get();

        return view('dashboard.admin.blogs.index', compact('blogs', 'categories'));
    }

    /**
     * Show the form for creating a new blog.
     */
    public function create()
    {
        $categories = BlogCategory::active()->get();
        $tags = BlogTag::orderBy('name')->get();

        return view('dashboard.admin.blogs.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created blog.
     */
    public function store(StoreBlogRequest $request)
    {
        $validated = $request->validated();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'blog-images/' . $imageName;

            // Ensure directory exists
            $directory = public_path('storage/blog-images');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image->move($directory, $imageName);
            $validated['featured_image'] = $imagePath;
        }

        // Set author
        $validated['author_id'] = Auth::id();

        // Set published_at if status is published
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Blog::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $blog = Blog::create($validated);

        // Handle tags
        if ($request->filled('tags')) {
            $this->syncTags($blog, $request->tags);
        }

        ActivityLogService::log('blog.created', "Blog '{$blog->title}' was created", $blog, [
            'blog_id' => $blog->id,
            'slug' => $blog->slug,
        ]);

        return redirect()->route('dashboard.admin.blogs.index')
            ->with('success', 'Blog created successfully.');
    }

    /**
     * Display the specified blog.
     */
    public function show(Blog $blog)
    {
        $blog->load(['author', 'category', 'tags']);
        return view('dashboard.admin.blogs.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified blog.
     */
    public function edit(Blog $blog)
    {
        $categories = BlogCategory::active()->get();
        $tags = BlogTag::orderBy('name')->get();
        $blog->load('tags');

        return view('dashboard.admin.blogs.edit', compact('blog', 'categories', 'tags'));
    }

    /**
     * Update the specified blog.
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        $validated = $request->validated();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($blog->featured_image) {
                $oldImagePath = public_path('storage/' . $blog->featured_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $image = $request->file('featured_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = 'blog-images/' . $imageName;

            // Ensure directory exists
            $directory = public_path('storage/blog-images');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $image->move($directory, $imageName);
            $validated['featured_image'] = $imagePath;
        } else {
            // Keep existing image
            unset($validated['featured_image']);
        }

        // Set published_at if status is changing to published
        if ($validated['status'] === 'published' && empty($blog->published_at)) {
            $validated['published_at'] = now();
        }

        // Generate slug if not provided and title changed
        if (empty($validated['slug']) && $blog->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (Blog::where('slug', $validated['slug'])->where('id', '!=', $blog->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        } elseif (empty($validated['slug'])) {
            unset($validated['slug']);
        }

        $blog->update($validated);

        // Handle tags
        if ($request->filled('tags')) {
            $this->syncTags($blog, $request->tags);
        } else {
            $blog->tags()->detach();
        }

        ActivityLogService::log('blog.updated', "Blog '{$blog->title}' was updated", $blog, [
            'blog_id' => $blog->id,
            'slug' => $blog->slug,
        ]);

        return redirect()->route('dashboard.admin.blogs.index')
            ->with('success', 'Blog updated successfully.');
    }

    /**
     * Remove the specified blog.
     */
    public function destroy(Blog $blog)
    {
        $title = $blog->title;
        $slug = $blog->slug;

        // Delete featured image if exists
        if ($blog->featured_image) {
            $imagePath = public_path('storage/' . $blog->featured_image);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $blog->delete();

        ActivityLogService::log('blog.deleted', "Blog '{$title}' was deleted", null, [
            'slug' => $slug,
        ]);

        return redirect()->route('dashboard.admin.blogs.index')
            ->with('success', 'Blog deleted successfully.');
    }

    /**
     * Toggle blog status (publish/draft).
     */
    public function toggleStatus(Request $request, Blog $blog)
    {

        $blog->status = $blog->status === 'published' ? 'draft' : 'published';

        if ($blog->status === 'published' && empty($blog->published_at)) {
            $blog->published_at = now();
        }

        $blog->save();

        ActivityLogService::log('blog.status.toggled', "Blog '{$blog->title}' status changed to {$blog->status}", $blog, [
            'blog_id' => $blog->id,
            'status' => $blog->status,
        ]);

        return redirect()->back()->with('success', "Blog status changed to {$blog->status}.");
    }

    /**
     * Sync tags for a blog.
     */
    private function syncTags(Blog $blog, string $tagsInput): void
    {
        // Parse comma-separated tags
        $tagNames = array_map('trim', explode(',', $tagsInput));
        $tagNames = array_filter($tagNames);
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (empty($tagName)) {
                continue;
            }

            // Find or create tag
            $tag = BlogTag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );

            $tagIds[] = $tag->id;
        }

        $blog->tags()->sync($tagIds);
    }
}
