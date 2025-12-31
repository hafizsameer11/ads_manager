<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
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

        $user = auth()->user();

        // Users with admin permissions don't need approval
        if ($user->hasAdminPermissions()) {
            return $next($request);
        }

        // Check account status - only allow approved users (is_active == 1)
        // 0 = rejected, 1 = approved, 2 = pending, 3 = suspended
        if ($user->is_active != 1) {
            return redirect()->route('pending-approval');
        }

        return $next($request);
    }
}