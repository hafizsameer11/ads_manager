<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Publisher;
use App\Models\Advertiser;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(Request $request)
    {
        $referralCode = $request->query('ref');
        return view('auth.register', compact('referralCode'));
    }

    /**
     * Handle a registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:publisher,advertiser',
            'referral_code' => 'nullable|exists:users,referral_code',
            'terms' => 'required|accepted',
        ]);

        $referrer = null;
        if ($request->referral_code) {
            $referrer = User::where('referral_code', $request->referral_code)->first();
        }

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'referral_code' => User::generateReferralCode(),
                'referred_by' => $referrer?->id,
                'is_active' => 1, // 1 = Approved (auto-approved on registration)
            ]);

            // Create role-specific profile
            if ($request->role === 'publisher') {
                Publisher::create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'tier' => 'tier3',
                    'approved_at' => now(),
                ]);
            } elseif ($request->role === 'advertiser') {
                Advertiser::create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'balance' => 0.00,
                    'total_spent' => 0.00,
                    'approved_at' => now(),
                ]);
            }

            // Create referral record if referred
            if ($referrer) {
                $referralService = app(ReferralService::class);
                $referralService->createReferral($referrer, $user, $request->role);
            }

            DB::commit();

            // Auto-login after registration
            Auth::login($user);

            // Redirect to dashboard (users are auto-approved)
            return redirect()->route('dashboard')
                    ->with('success', 'Registration successful! Welcome to your dashboard.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}