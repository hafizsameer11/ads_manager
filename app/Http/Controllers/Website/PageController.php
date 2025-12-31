<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    /**
     * Display a page by slug.
     */
    public function show(string $slug)
    {
        $page = Page::findBySlug($slug);

        if (!$page) {
            abort(404, 'Page not found');
        }

        return view('website.pages.show', compact('page'));
    }
}
