<?php

namespace Database\Seeders;

use App\Models\SubscriptionTiersModel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(SubscriptionTierMasterSeeder::class);
        
        $premuimTier = SubscriptionTiersModel::where('name', 'premium')->value('id');
        User::firstOrCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ],[
            'password' => bcrypt('password'),
            'subscription_tier_id' => $premuimTier,
            'api_key' => bin2hex(random_bytes(16)),
        ]);
    }
}
