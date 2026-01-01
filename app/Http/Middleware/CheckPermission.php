<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * 
     * STRICT RULE: Admin (role === 'admin') bypasses ALL permission checks.
     * Permissions are ONLY checked for Sub-Admins.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle($request, \Closure $next, ...$permissions)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'You must be authenticated to access this resource.');
        }

        // HARD RULE: Admin bypasses ALL permission checks
        if ($user->role === 'admin' || $user->hasRole('admin')) {
            return $next($request);
        }

        // For Sub-Admins: Check if user has any of the required permissions
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    return $next($request);
                }
            }
        }

        // If no permissions specified and user is not admin, deny access
        abort(403, 'You do not have permission to access this resource.');
    }
}
