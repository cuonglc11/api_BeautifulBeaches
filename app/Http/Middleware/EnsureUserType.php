<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Nếu yêu cầu là admin nhưng user không phải User model
        if ($type === 'admin' && !($user instanceof \App\Models\User)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: only admin can access this route.'
            ], 403);
        }

        // Nếu yêu cầu là customer nhưng user không phải Account model
        if ($type === 'customer' && !($user instanceof \App\Models\Account)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: only customer can access this route.'
            ], 403);
        }

        return $next($request);
    }
}