<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    /**
     * Display the publisher page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('website.pages.publisher');
    }
}




