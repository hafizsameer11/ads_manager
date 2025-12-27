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
        $validated = $request->validate([
            'type' => 'required|string|in:fraud,spam,inappropriate,copyright,other',
            'url' => 'nullable|url|max:500',
            'email' => 'required|email|max:255',
            'description' => 'required|string|min:10',
        ]);

        // Create abuse report
        $report = \App\Models\AbuseReport::create([
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'email' => $validated['email'],
            'description' => $validated['description'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Send email to admin
        try {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\AbuseReportMail($report));
        } catch (\Exception $e) {
            \Log::error('Failed to send abuse report email: ' . $e->getMessage());
        }

        return redirect()->route('website.report-abuse')
            ->with('success', 'Thank you for your report. We will review it and take appropriate action.');
    }
}




