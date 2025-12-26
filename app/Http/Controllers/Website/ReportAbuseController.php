<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportAbuseController extends Controller
{
    /**
     * Display the report abuse page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('website.pages.report-abuse');
    }

    /**
     * Handle the report abuse form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Add validation and abuse report handling logic here
        $validated = $request->validate([
            'type' => 'required|string|in:fraud,spam,inappropriate,copyright,other',
            'url' => 'nullable|url|max:500',
            'email' => 'required|email|max:255',
            'description' => 'required|string|min:10',
        ]);

        // TODO: Add logic to save abuse report to database and notify admins
        // Example: AbuseReport::create($validated);

        return redirect()->route('website.report-abuse')
            ->with('success', 'Thank you for your report. We will review it and take appropriate action.');
    }
}




