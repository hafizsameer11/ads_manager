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
        // Add validation and DMCA report handling logic here
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

        // TODO: Add logic to save DMCA report to database and notify admins
        // Example: DmcaReport::create($validated);

        return redirect()->route('website.report-dmca')
            ->with('success', 'Your DMCA notice has been received. We will process it according to our DMCA policy.');
    }
}




