<?php

namespace App\Http\Middleware;

use App\Models\ApiLogModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackApiUsage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);

        $user = $request->user();

        ApiLogModel::create([
            'user_id'       => $user?->id,
            'endpoint'      => $request->path(),
            'method'        => $request->method(),
            'status_code'   => $response->status(),
            'response_time_ms' => (int) ((microtime(true) - $start) * 1000),
            'requested_at'  => now(),
        ]);

        return $response;
    }
}
