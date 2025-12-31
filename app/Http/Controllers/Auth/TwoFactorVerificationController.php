<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Validation\ValidationException;

class TwoFactorVerificationController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the 2FA verification form.
     */
    public function show()
    {
        // Check if user ID is stored in session (from login)
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }

        $userId = session('login.id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget('login.id');
            return redirect()->route('login');
        }

        return view('auth.two-factor-verify');
    }

    /**
     * Verify 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        // Get user ID from session
        if (!session()->has('login.id')) {
            return redirect()->route('login');
        }

        $userId = session('login.id');
        $user = \App\Models\User::find($userId);

        if (!$user || !$user->hasTwoFactorEnabled()) {
            session()->forget('login.id');
            return redirect()->route('login');
        }

        $code = $request->code;
        $secret = decrypt($user->two_factor_secret);

        // Check if it's a recovery code
        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $isRecoveryCode = false;

        foreach ($recoveryCodes as $index => $hashedCode) {
            if (Hash::check($code, $hashedCode)) {
                // Remove used recovery code
                unset($recoveryCodes[$index]);
                $user->update(['two_factor_recovery_codes' => array_values($recoveryCodes)]);
                $isRecoveryCode = true;
                break;
            }
        }

        // Verify OTP if not a recovery code
        if (!$isRecoveryCode) {
            $valid = $this->google2fa->verifyKey($secret, $code, 2); // 2 windows tolerance

            if (!$valid) {
                // Log failed attempt
                ActivityLogService::logTwoFactorFailed($user, 'Invalid OTP');

                return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
            }
        }

        // Clear session and log in user
        session()->forget('login.id');
        Auth::login($user, session('login.remember', false));
        session()->forget('login.remember');

        // Log successful verification
        ActivityLogService::logTwoFactorVerified($user);

        // Redirect to intended destination or dashboard
        return redirect()->intended($this->getRedirectPath($user));
    }

    /**
     * Get redirect path based on user role.
     */
    protected function getRedirectPath($user)
    {
        if ($user->hasAdminPermissions()) {
            return route('dashboard.admin.home');
        } elseif ($user->isPublisher()) {
            return route('dashboard.publisher.home');
        } elseif ($user->isAdvertiser()) {
            return route('dashboard.advertiser.home');
        }

        return route('website.home');
    }
}
