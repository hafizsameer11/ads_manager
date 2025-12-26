<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            // Redirect to appropriate dashboard or show 403
            if ($user->isAdmin()) {
                return redirect()->route('dashboard.admin.home');
            } elseif ($user->isPublisher()) {
                return redirect()->route('dashboard.publisher.home');
            } elseif ($user->isAdvertiser()) {
                return redirect()->route('dashboard.advertiser.home');
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}