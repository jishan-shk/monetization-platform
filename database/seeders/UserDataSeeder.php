<?php

namespace Database\Seeders;

use App\Models\SubscriptionTiersModel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = SubscriptionTiersModel::all();

        foreach ($tiers as $tier) {
            User::updateOrCreate(
                [
                    'email' => "{$tier->name}@example.com",
                ],
                [
                    'name' => ucfirst($tier->name) . ' User',
                    'password' => Hash::make('password'),
                    'subscription_tier_id' => $tier->id,
                    'api_key' => 'API_KEY_' . strtoupper($tier->name),
                ]
            );
        }
    }
}
