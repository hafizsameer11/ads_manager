<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Redirect authenticated users to their appropriate dashboard
                if ($user->hasAdminPermissions()) {
                    return redirect()->route('dashboard.admin.home');
                } elseif ($user->isPublisher()) {
                    return redirect()->route('dashboard.publisher.home');
                } elseif ($user->isAdvertiser()) {
                    return redirect()->route('dashboard.advertiser.home');
                }
                
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
