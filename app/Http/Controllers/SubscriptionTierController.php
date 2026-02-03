<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionTiersModel;
use App\UsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubscriptionTierController extends Controller
{
    public function list()
    {
        $tiers = SubscriptionTiersModel::select(['name', 'daily_limit', 'extra_call_rate'])
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $tiers
        ]);
    }

    public function current_usage(Request $request,UsageService $usageService)
    {
        $user = $request->user();
        $tier = $user->tier;

        $current_usage = $usageService->getCurrentUsage($user);
        $total_limit = $tier->daily_limit;

        return response()->json([
            'status' => true,
            'data'   => [
                'total_available' => $total_limit,
                'current_usage'   => $current_usage,
                'available_usage' => $total_limit - $current_usage
            ]
        ]);
    }
}
