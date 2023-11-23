<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OperatorMiddleware
{
    public function handle($request, Closure $next)
    {
        // Pemeriksaan role_id untuk operator
        if ($request->user() && $request->user()->role_id >= 50) {
            return $next($request);
        }

        return response()->json(['error' => 'Akses ditolak'], 403);
    }
}
