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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle($request, \Closure $next, ...$permissions)
    {
        $user = auth()->user();

        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this resource.');
    }
}
