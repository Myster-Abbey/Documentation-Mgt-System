<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\helper\General;
use App\Models\User;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // if (!auth()->user() || !auth()->user()->is_admin) {
        //     return General::apiFailureResponse('Unauthorized access', 403);
        // }
        $user = User::query()->find($request->user_id);
        $role = null;
        if ($user) {
            $role = $user->role;
        }

        if (strtolower($role) != 'admin') {
            return General::apiFailureResponse('You do not have permission', 401);
        }

        return $next($request);
    }
}

