<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\helper\General;
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user() || !auth()->user()->is_admin) {
            return General::apiFailureResponse('Unauthorized access', 403);
        }

        return $next($request);
    }
}

