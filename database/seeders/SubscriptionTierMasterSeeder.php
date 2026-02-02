<?php

namespace Database\Seeders;

use App\Models\SubscriptionTiersModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionTierMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionTiersModel::insert([
            ['name' => 'free', 'daily_limit' => 100],
            ['name' => 'standard', 'daily_limit' => 1000],
            ['name' => 'premium', 'daily_limit' => 10000, 'extra_call_rate' => 0.01],
        ]);
    }
}
