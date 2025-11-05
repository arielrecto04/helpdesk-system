<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            abort(401);
        }

        // Check if user has any of the required roles
        $hasRole = $request->user()->roles()->whereIn('name', $roles)->exists();
        
        if (!$hasRole) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}