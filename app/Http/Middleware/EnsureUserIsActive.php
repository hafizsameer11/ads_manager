<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Only allow access if user is approved (is_active == 1)
        $user = auth()->user();
        if ($user->is_active != 1) {
            $isActive = $user->is_active;
            auth()->logout();
            
            if ($isActive == 0) {
                $message = 'Your account has been rejected. Please contact support.';
            } elseif ($isActive == 2) {
                $message = 'Your account is pending approval. Please wait for admin approval.';
            } else {
                $message = 'Your account is not active. Please contact support.';
            }
            return redirect()->route('login')
                ->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}