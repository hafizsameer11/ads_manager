<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of published blogs.
     */
    public function index(Request $request)
    {
        $query = Blog::with(['author', 'category', 'tags'])
            ->published()
            ->latest('published_at');

        // Filter by category
        if ($request->has('category')) {
            $categoryId = $request->category;
            if (is_numeric($categoryId)) {
                $query->where('category_id', $categoryId);
            }
        }

        // Filter by tag
        if ($request->has('tag')) {
            $tag = BlogTag::where('slug', $request->tag)->first();
            if ($tag) {
                $query->byTag($tag->id);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->paginate(12);
        $categories = BlogCategory::active()->withCount('blogs')->get();
        $popularTags = BlogTag::withCount('blogs')
            ->orderBy('blogs_count', 'desc')
            ->limit(10)
            ->get();

        return view('website.blog.index', compact('blogs', 'categories', 'popularTags'));
    }

    /**
     * Display the specified blog.
     */
    public function show($slug)
    {
        $blog = Blog::with(['author', 'category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $blog->incrementViews();

        // Get related blogs (same category, exclude current)
        $relatedBlogs = Blog::with(['author', 'category'])
            ->published()
            ->where('id', '!=', $blog->id);
        
        if ($blog->category_id) {
            $relatedBlogs->where('category_id', $blog->category_id);
        }
        
        $relatedBlogs = $relatedBlogs->latest('published_at')
            ->limit(3)
            ->get();

        return view('website.blog.show', compact('blog', 'relatedBlogs'));
    }
}
