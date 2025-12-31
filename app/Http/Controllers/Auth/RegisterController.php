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
                'is_active' => 2, // 2 = Pending (default for new users)
            ]);

            // Create role-specific profile
            if ($request->role === 'publisher') {
                Publisher::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'tier' => 'tier3',
                ]);
            } elseif ($request->role === 'advertiser') {
                Advertiser::create([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'balance' => 0.00,
                    'total_spent' => 0.00,
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

            // Redirect to pending approval page (users are not approved by default)
            return redirect()->route('pending-approval')
                    ->with('success', 'Registration successful! Your account is pending approval.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed. Please try again.'])->withInput();
        }
    }
}