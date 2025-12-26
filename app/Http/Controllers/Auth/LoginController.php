<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Accept either username or email
        $username = $request->input('username');
        $email = $request->input('email');
        
        // Determine which field is being used
        if (!empty($username)) {
            $loginField = 'username';
            $loginValue = $username;
            $validationRules = ['username' => 'required|string', 'password' => 'required|string'];
        } else {
            $loginField = 'email';
            $loginValue = $email;
            $validationRules = ['email' => 'required|email', 'password' => 'required|string'];
        }
        
        $request->validate($validationRules);

        // Build credentials array
        $credentials = [
            $loginField => $loginValue,
            'password' => $request->password,
        ];
        
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    $loginField => 'Your account has been deactivated. Please contact support.',
                ]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            $loginField => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Redirect user based on their role.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectBasedOnRole($user)
    {
        if ($user->isAdmin()) {
            return redirect()->intended(route('dashboard.admin.home'));
        } elseif ($user->isPublisher()) {
            return redirect()->intended(route('dashboard.publisher.home'));
        } elseif ($user->isAdvertiser()) {
            return redirect()->intended(route('dashboard.advertiser.home'));
        }

        return redirect()->route('website.home');
    }

    /**
     * Log the user out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('website.home')->with('success', 'You have been logged out successfully.');
    }
}