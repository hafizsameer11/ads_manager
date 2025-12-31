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
        // Accept either username or email from 'login' field, or fallback to 'username'/'email' for backward compatibility
        $login = $request->input('login') ?: $request->input('username') ?: $request->input('email');
        
        // Validate that we have a login value and password
        $request->validate([
            'login' => 'sometimes|string',
            'username' => 'sometimes|string',
            'email' => 'sometimes|email',
            'password' => 'required|string',
        ]);

        if (empty($login)) {
            throw ValidationException::withMessages([
                'login' => ['Please provide either username or email.'],
            ]);
        }

        $password = $request->password;
        $remember = $request->boolean('remember');

        // Determine if the input looks like an email
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        // Try to authenticate with email first if it looks like an email, otherwise try username
        // If first attempt fails, try the other method as fallback
        $authenticated = false;
        $loginField = null;

        if ($isEmail) {
            // Try email first
            $credentials = ['email' => $login, 'password' => $password];
            if (Auth::attempt($credentials, $remember)) {
                $authenticated = true;
                $loginField = 'email';
            } else {
                // Fallback to username
                $credentials = ['username' => $login, 'password' => $password];
                if (Auth::attempt($credentials, $remember)) {
                    $authenticated = true;
                    $loginField = 'username';
                }
            }
        } else {
            // Try username first
            $credentials = ['username' => $login, 'password' => $password];
            if (Auth::attempt($credentials, $remember)) {
                $authenticated = true;
                $loginField = 'username';
            } else {
                // Fallback to email
                $credentials = ['email' => $login, 'password' => $password];
                if (Auth::attempt($credentials, $remember)) {
                    $authenticated = true;
                    $loginField = 'email';
                }
            }
        }

        if ($authenticated) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user is approved (only is_active == 1 can login)
            if ($user->is_active != 1) {
                Auth::logout();
                if ($user->is_active == 0) {
                    $message = 'Your account has been rejected. Please contact support.';
                } elseif ($user->is_active == 2) {
                    $message = 'Your account is pending approval. Please wait for admin approval.';
                } else {
                    $message = 'Your account is not active. Please contact support.';
                }
                return back()->withErrors([
                    'login' => $message,
                ]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'login' => ['The provided credentials do not match our records.'],
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