<?php

namespace App\Jobs;

use App\Models\BillingModel;
use App\Models\SubscriptionTiersModel;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyBilling implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try{
            $lastMonth = now()->subMonth();
            $billingData = [];

            $premiumUsers = User::whereHas('tier', fn($q) => $q->where('name', 'premium'))
                ->whereHas('api_usage', function ($query) use ($lastMonth) {
                    $query->whereYear('date', $lastMonth->year)
                        ->whereMonth('date', $lastMonth->month);
                })
                ->withSum(['api_usage as extra_calls' => function ($query) use ($lastMonth) {
                    $query->whereYear('date', $lastMonth->year)
                        ->whereMonth('date', $lastMonth->month);
                }], 'count')
                ->get();

            $tier = SubscriptionTiersModel::where('name', 'premium')->first();
            foreach ($premiumUsers as $user) {
                $extra_calls = $user->extra_calls ?? 0;
            
                $billingData = [
                    'user_id'       => $user->id,
                    'extra_calls'   => $extra_calls,
                    'amount'        => $extra_calls * 0.01,
                ];

                BillingModel::updateOrCreate([
                    'user_id'       => $user->id,
                    'billing_month' => $lastMonth->startOfMonth()->toDateString(),
                ], $billingData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing monthly billing: ' . $e->getMessage());
        }
    }
}
