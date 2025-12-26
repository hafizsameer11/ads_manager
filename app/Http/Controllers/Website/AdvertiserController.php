<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdvertiserController extends Controller
{
    /**
     * Display the advertiser page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('website.pages.advertiser');
    }
}




