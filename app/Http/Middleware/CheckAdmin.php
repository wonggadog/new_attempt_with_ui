<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->department !== 'Admin') {
            abort(403, 'Unauthorized. Admins only.');
        }
        return $next($request);
    }
} 