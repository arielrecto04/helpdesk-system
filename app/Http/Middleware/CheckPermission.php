<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // If the user is not logged in, redirect to login with a polite message.
        if (!$request->user()) {
            if ($request->expectsJson()) {
                abort(401, '🔒 Please log in to continue.');
            }
            return redirect()->guest(route('login'))->with('error', '🔒 Please log in to continue.');
        }

        // Support multiple permissions separated by `|` or `,`. If any permission matches, allow.
        $givenPermissions = preg_split('/\||,/', $permission);
        $givenPermissions = array_map('trim', $givenPermissions);

        $hasAnyPermission = false;
        foreach ($givenPermissions as $perm) {
            if ($perm === '') {
                continue;
            }
            if ($request->user()->hasPermissionTo($perm)) {
                $hasAnyPermission = true;
                break;
            }
        }

        // Check if user has the required permission(s). If not, return a polite 403.
        if (!$hasAnyPermission) {
            $message = '🚫 Sorry — you do not have permission to view this page. If you believe this is an error, please contact your system administrator. 😢';
            // Log the denied attempt for auditing
            try {
                Log::info('Permission denied', ['user_id' => $request->user()->id, 'permission' => $permission, 'path' => $request->path()]);
            } catch (\Throwable $e) {
                // Ignore logging errors to avoid breaking the request flow
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
