<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClearLegacyAdminLogin
{
    public function handle(Request $request, Closure $next)
    {
        // Remove administrator logins left in the public guard by the previous shared session.
        if (Auth::guard('web')->user()?->role === 'admin') {
            Auth::guard('web')->logout();
        }

        return $next($request);
    }
}
