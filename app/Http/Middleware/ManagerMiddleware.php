<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle($request, Closure $next)
    {
        // Pemeriksaan role_id untuk operator
        if ($request->user() && $request->user()->role_id >= 90) {
            return $next($request);
        }

        return response()->json([
            'error' => 'Access Denied'],
             403
        );
    }
}
