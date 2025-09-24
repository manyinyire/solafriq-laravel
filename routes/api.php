<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\SolarSystemController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API routes
Route::prefix('v1')->group(function () {
    
    // Solar Systems (public endpoints)
    Route::get('solar-systems', [SolarSystemController::class, 'index']);
    Route::get('solar-systems/{solarSystem}', [SolarSystemController::class, 'show']);
    
    // Orders (guest checkout)
    Route::post('orders', [OrderController::class, 'store']);
    
});

// Authenticated API routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // User Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/recent-orders', [DashboardController::class, 'recentOrders']);
    Route::get('dashboard/installment-summary', [DashboardController::class, 'installmentSummary']);
    
    // Orders (authenticated users)
    
    
});


// Webhook routes (for payment gateways, etc.)
Route::prefix('webhooks')->group(function () {
    Route::post('paystack', [PaymentWebhookController::class, 'paystack']);
    Route::post('flutterwave', [PaymentWebhookController::class, 'flutterwave']);
});

// Health check
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'SolaFriq API',
        'version' => '1.0.0'
    ]);
});