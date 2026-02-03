<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->role != ROLE_ADMIN) {
            return response()->json([
                'error' => UNAUTHORIZED_ERROR_MSG 
            ], UNAUTHORIZED_ACCESS_CODE);    
        }

        return $next($request);
    }
}
