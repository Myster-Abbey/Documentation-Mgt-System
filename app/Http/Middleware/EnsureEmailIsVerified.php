<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Your email address is not verified.',
            ], 403);
        }

        return $next($request);
    }
}
