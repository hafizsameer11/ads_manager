<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class BlogCategoriesController extends Controller
{
    /**
     * Display a listing of blog categories.
     */
    public function index()
    {
        $categories = BlogCategory::withCount('blogs')->latest()->get();
        return view('dashboard.admin.blog-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('dashboard.admin.blog-categories.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category = BlogCategory::create($validated);

        ActivityLogService::log('blog_category.created', "Blog category '{$category->name}' was created", $category, [
            'category_id' => $category->id,
        ]);

        return redirect()->route('dashboard.admin.blog-categories.index')
            ->with('success', 'Blog category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(BlogCategory $blogCategory)
    {
        $blogCategory->loadCount('blogs');
        return view('dashboard.admin.blog-categories.show', compact('blogCategory'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(BlogCategory $blogCategory)
    {
        return view('dashboard.admin.blog-categories.edit', compact('blogCategory'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, BlogCategory $blogCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $blogCategory->id,
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $blogCategory->update($validated);

        ActivityLogService::log('blog_category.updated', "Blog category '{$blogCategory->name}' was updated", $blogCategory, [
            'category_id' => $blogCategory->id,
        ]);

        return redirect()->route('dashboard.admin.blog-categories.index')
            ->with('success', 'Blog category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(BlogCategory $blogCategory)
    {
        $name = $blogCategory->name;

        // Check if category has blogs
        if ($blogCategory->blogs()->count() > 0) {
            return redirect()->route('dashboard.admin.blog-categories.index')
                ->with('error', 'Cannot delete category with existing blogs. Please reassign or delete the blogs first.');
        }

        $blogCategory->delete();

        ActivityLogService::log('blog_category.deleted', "Blog category '{$name}' was deleted", null, []);

        return redirect()->route('dashboard.admin.blog-categories.index')
            ->with('success', 'Blog category deleted successfully.');
    }
}
