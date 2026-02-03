<?php

namespace App\Http\Middleware;

use App\UsageService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected $usageService;

    public function __construct(UsageService $usageService) {
        $this->usageService = $usageService;
    }

    public function handle($request, Closure $next)
    {
        $user = $request->user();
        
        if(!$this->usageService->verifyApiKey($user,$request)){
            return response()->json([
                'error' => UNAUTHORIZED_ERROR_MSG 
            ], UNAUTHORIZED_ACCESS_CODE);
        }
        if ($this->usageService->hasExceededLimit($user)) {
            return response()->json([
                'error' => TOO_MANY_ATTEMPT_MESSAGE 
            ], TOO_MANY_ATTEMPT_CODE);
        }

        $this->usageService->incrementUsage($user);
        
        return $next($request);
    }
}
