<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportDmcaController extends Controller
{
    /**
     * Display the DMCA report page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('website.pages.report-dmca');
    }

    /**
     * Handle the DMCA report form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'copyright_owner' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'infringing_url' => 'required|url|max:500',
            'original_work' => 'required|string|min:10',
            'statement' => 'required|string|min:10',
            'accuracy' => 'required|accepted',
        ]);

        // Create DMCA report
        $report = \App\Models\DmcaReport::create([
            'copyright_owner' => $validated['copyright_owner'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'infringing_url' => $validated['infringing_url'],
            'original_work' => $validated['original_work'],
            'statement' => $validated['statement'],
            'accuracy_confirmed' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Send email to admin
        try {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\DmcaReportMail($report));
        } catch (\Exception $e) {
            \Log::error('Failed to send DMCA report email: ' . $e->getMessage());
        }

        return redirect()->route('website.report-dmca')
            ->with('success', 'Your DMCA notice has been received. We will process it according to our DMCA policy.');
    }
}




