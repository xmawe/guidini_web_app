<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
