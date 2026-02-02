<?php

namespace App;

use App\Models\ApiUsageModel;
use App\Models\ApiLogModel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsageService
{
    /**
     * Create a new class instance.
     */
    public function getUsageKey(User $user): string
    {
        return "usage:{$user->id}:" . now()->format('Y-m-d');
    }

    public function incrementUsage(User $user)
    {
        $key = $this->getUsageKey($user);
        Cache::increment($key);
    }

    public function hasExceededLimit(User $user): bool
    {
        $tier = $user->tier;
        $limit = $tier->daily_limit;
        
        $currentUsage = Cache::get($this->getUsageKey($user), 0);

        if ($currentUsage >= $limit) {
            if (strtolower($tier->name) =='premium') {
                ApiUsageModel::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date'    => now()->toDateString(),
                    ],
                    [
                        'count'        => DB::raw('count + 1'),
                        'request_path' => request()->path(),
                        'updated_at'   => now(),
                    ]
                );
                return false;
            }

            return true;
        }

        return false;
    }

    public function save_api_log(User $user, $request,$status_code) {
          ApiLogModel::create([
            'user_id' => $user->id,
            'endpoint' => $request->path(),
            'status_code' => $status_code
        ]);
    }

    public function verifyApiKey(User $user,$request)
    {
        return $user->api_key == $request->header('X-API-KEY');
    }
}

