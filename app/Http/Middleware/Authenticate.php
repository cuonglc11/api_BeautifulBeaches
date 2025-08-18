<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Nếu là API → trả JSON
        if ($request->is('api/*')) {
            abort(response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Missing or invalid token.'
            ], 401));
        }

        // Nếu là WEB → redirect về login
        return route('login');
    }
}
