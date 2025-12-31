<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show 2FA setup page.
     */
    public function show()
    {
        $user = Auth::user();
        $qrCodeUrl = null;
        $secret = null;

        if (!$user->hasTwoFactorEnabled()) {
            // Generate secret for new setup
            $secret = $this->google2fa->generateSecretKey();
            $qrCodeUrl = $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->email,
                $secret
            );
        }

        return view('dashboard.admin.security.two-factor', compact('user', 'qrCodeUrl', 'secret'));
    }

    /**
     * Enable 2FA by confirming OTP.
     */
    public function enable(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'secret' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        // Verify OTP
        $valid = $this->google2fa->verifyKey($request->secret, $request->otp, 2); // 2 windows tolerance

        if (!$valid) {
            // Log failed attempt
            ActivityLogService::logTwoFactorFailed($user, 'Invalid OTP during setup');

            return back()->withErrors(['otp' => 'Invalid verification code. Please try again.']);
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        $hashedCodes = array_map(fn($code) => Hash::make($code), $recoveryCodes);

        // Save 2FA secret and recovery codes
        $user->update([
            'two_factor_secret' => encrypt($request->secret),
            'two_factor_recovery_codes' => $hashedCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        // Log 2FA enabled
        ActivityLogService::logTwoFactorEnabled($user);

        // Store recovery codes in session temporarily to show to user
        session()->flash('recovery_codes', $recoveryCodes);

        return redirect()->route('dashboard.admin.security.two-factor')
            ->with('success', 'Two-factor authentication has been enabled successfully. Please save your recovery codes.');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Disable 2FA
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        // Log 2FA disabled
        ActivityLogService::logTwoFactorDisabled($user);

        return redirect()->route('dashboard.admin.security.two-factor')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes()
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard.admin.security.two-factor')
                ->withErrors(['error' => 'Two-factor authentication is not enabled.']);
        }

        // Regenerate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();
        $hashedCodes = array_map(fn($code) => Hash::make($code), $recoveryCodes);

        $user->update([
            'two_factor_recovery_codes' => $hashedCodes,
        ]);

        // Log recovery codes regenerated
        ActivityLogService::log('two_factor.recovery_codes_regenerated', "Recovery codes regenerated for user '{$user->name}'", $user, [
            'user_name' => $user->name,
            'user_email' => $user->email,
        ]);

        return view('dashboard.admin.security.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Generate recovery codes.
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
        }
        return $codes;
    }
}
