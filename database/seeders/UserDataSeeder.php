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
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        $tiers = SubscriptionTiersModel::all();

        foreach ($tiers as $tier) {
            User::updateOrCreate(
                [
                    'email' => "{$tier->name}@gmail.com",
                ],
                [
                    'name' => ucfirst($tier->name) . ' User',
                    'password' => 'password',
                    'subscription_tier_id' => $tier->id,
                    'api_key' => 'API_KEY_' . strtoupper($tier->name),
                ]
            );
        }
    }
}
