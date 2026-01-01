<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Log the status for debugging
            Log::info('Password reset link request', [
                'email' => $request->email,
                'status' => $status,
                'mail_driver' => config('mail.default'),
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                return back()->with('status', 'We have emailed your password reset link! Please check your inbox.');
            }

            // If the email doesn't exist, we still show success message for security
            // This prevents email enumeration attacks
            if ($status === Password::INVALID_USER) {
                return back()->with('status', 'If that email address exists in our system, we have sent a password reset link to it.');
            }

            // For other errors, show the actual error message
            Log::warning('Password reset failed', [
                'email' => $request->email,
                'status' => $status,
            ]);
            
            return back()->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            Log::error('Password reset exception', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors(['email' => 'An error occurred while sending the password reset link. Please try again later or contact support.']);
        }
    }
}