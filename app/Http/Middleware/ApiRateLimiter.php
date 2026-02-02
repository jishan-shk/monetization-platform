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

    public function handle($request, Closure $next,UsageService $usageService)
    {
        $user = $request->user();
        
        if(!$this->usageService->verifyApiKey($user,$request)){
            return response()->json([
                'error' => 'Unauthorized Access.' 
            ], 401);
        }
        
        if ($this->usageService->hasExceededLimit($user)) {
            $this->usageService->save_api_log($user, $request, 429);

            return response()->json([
                'error' => '429 Too Many Requests.' 
            ], 429);
        }

        $this->usageService->incrementUsage($user);
        $this->usageService->save_api_log($user, $request, 200);
        
        return $next($request);
    }
}
