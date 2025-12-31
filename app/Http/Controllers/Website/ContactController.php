<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('website.pages.contact');
    }

    /**
     * Handle the contact form submission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        // Create contact submission
        $submission = \App\Models\ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify admin about new contact message
        \App\Services\NotificationService::notifyAdmins(
            'contact_message_received',
            'general',
            'New Contact Message',
            "A new contact message has been received from {$validated['name']} ({$validated['email']}): {$validated['subject']}",
            ['contact_submission_id' => $submission->id, 'name' => $validated['name'], 'email' => $validated['email']]
        );

        // Send email to admin
        try {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            \Illuminate\Support\Facades\Mail::to($adminEmail)->send(new \App\Mail\ContactFormMail($submission));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::error('Failed to send contact form email: ' . $e->getMessage());
        }

        return redirect()->route('website.contact')
            ->with('success', 'Thank you for your message. We will get back to you soon!');
    }
}




