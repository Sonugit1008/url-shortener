<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response {

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userRole = $user->role?->name;

        if (!$userRole || !in_array($userRole, $roles, true)) {

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to access this resource.',
                ], 403);
            }

            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
