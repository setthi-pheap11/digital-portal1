<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSeller
{
    public function handle(Request $request, Closure $next)
    {
        // if ($request->user() && $request->user()->role !== 'seller') {
        //     return response()->json(['message' => 'Access denied. Only sellers can access this.'], 403);
        // }

        return $next($request);
    }
}
