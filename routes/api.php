<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionTierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('generate-email-otp', [AuthController::class, 'generateEmailOtp']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('subscription-tier-list',[SubscriptionTierController::class, 'list']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('current-usage',[SubscriptionTierController::class, 'current_usage']);
    Route::get('/data', function () {
        return response()->json([
            'data' => 'Sample protected Api'
        ]);
    })->middleware(['api.tier_rate_limiter']);
});