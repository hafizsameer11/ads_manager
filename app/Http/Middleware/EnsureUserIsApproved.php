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

        // Admin doesn't need approval
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check publisher approval
        if ($user->isPublisher()) {
            if (!$user->publisher || $user->publisher->status !== 'approved') {
                return redirect()->route('pending-approval');
            }
        }

        // Check advertiser approval
        if ($user->isAdvertiser()) {
            if (!$user->advertiser || $user->advertiser->status !== 'approved') {
                return redirect()->route('pending-approval');
            }
        }

        return $next($request);
    }
}