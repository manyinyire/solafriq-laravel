<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| Note: These are TRUE REST API routes for external consumers.
| Internal Inertia.js routes remain in web.php for proper session handling.
|
*/

// Public API Routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Custom Builder - Public endpoints
    Route::get('/custom-builder/products', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'getProducts']);
    Route::post('/custom-builder/calculate', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'calculate']);
    Route::post('/custom-builder/validate', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'validateSystem']);
});

// Protected API Routes (require authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Dashboard Stats
    Route::get('/dashboard/stats', [App\Http\Controllers\Api\V1\DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-orders', [App\Http\Controllers\Api\V1\DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/installment-summary', [App\Http\Controllers\Api\V1\DashboardController::class, 'installmentSummary']);
    Route::get('/dashboard/warranty-summary', [App\Http\Controllers\Api\V1\DashboardController::class, 'warrantySummary']);

    // Orders
    Route::get('/orders', [App\Http\Controllers\Api\V1\OrderController::class, 'index']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\V1\OrderController::class, 'show']);
    Route::put('/orders/{order}', [App\Http\Controllers\Api\V1\OrderController::class, 'update']);

    // Installment Plans
    Route::apiResource('installment-plans', App\Http\Controllers\Api\V1\InstallmentPlanController::class);
    Route::post('/installment-plans/{installmentPlan}/payments/{payment}/pay', [App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'processPayment']);

    // Warranties
    Route::get('/warranties', [App\Http\Controllers\Api\V1\WarrantyController::class, 'index']);
    Route::get('/warranties/{warranty}', [App\Http\Controllers\Api\V1\WarrantyController::class, 'show']);
    Route::get('/warranties/{warranty}/certificate', [App\Http\Controllers\Api\V1\WarrantyController::class, 'downloadCertificate']);
    Route::post('/warranties/{warranty}/claims', [App\Http\Controllers\Api\V1\WarrantyController::class, 'createClaim']);
    Route::get('/warranty-claims', [App\Http\Controllers\Api\V1\WarrantyController::class, 'claims']);

    // Custom Builder - Authenticated
    Route::post('/custom-builder/save', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'save']);
    Route::get('/custom-builder/saved', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'saved']);
    Route::post('/custom-builder/add-to-cart', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'addToCart']);
});

// Admin API Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/overview', [App\Http\Controllers\Api\V1\DashboardController::class, 'adminOverview']);
    Route::get('/dashboard/sales-analytics', [App\Http\Controllers\Api\V1\DashboardController::class, 'salesAnalytics']);
    Route::get('/dashboard/system-metrics', [App\Http\Controllers\Api\V1\DashboardController::class, 'systemMetrics']);

    // Orders Management
    Route::get('/orders', [App\Http\Controllers\Api\V1\OrderController::class, 'index']);
    Route::get('/orders/{order}', [App\Http\Controllers\Api\V1\OrderController::class, 'show']);
    Route::put('/orders/{order}', [App\Http\Controllers\Api\V1\OrderController::class, 'update']);
    Route::put('/orders/{order}/accept', [App\Http\Controllers\Api\V1\OrderController::class, 'accept']);
    Route::put('/orders/{order}/decline', [App\Http\Controllers\Api\V1\OrderController::class, 'decline']);
    Route::put('/orders/{order}/confirm-payment', [App\Http\Controllers\Api\V1\OrderController::class, 'confirmPayment']);
    Route::put('/orders/{order}/status', [App\Http\Controllers\Api\V1\OrderController::class, 'updateStatus']);
    Route::put('/orders/{order}/schedule-installation', [App\Http\Controllers\Api\V1\OrderController::class, 'scheduleInstallation']);
    Route::put('/orders/{order}/tracking', [App\Http\Controllers\Api\V1\OrderController::class, 'updateTracking']);
    Route::post('/orders/{order}/notes', [App\Http\Controllers\Api\V1\OrderController::class, 'addNote']);
    Route::post('/orders/{order}/refund', [App\Http\Controllers\Api\V1\OrderController::class, 'refund']);
    Route::post('/orders/{order}/resend-notification', [App\Http\Controllers\Api\V1\OrderController::class, 'resendNotification']);
    Route::get('/orders/{order}/invoice-pdf', [App\Http\Controllers\Api\V1\OrderController::class, 'downloadInvoice']);
    Route::get('/installations', [App\Http\Controllers\Api\V1\OrderController::class, 'scheduledInstallations']);

    // Installment Plans
    Route::get('/installment-plans', [App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'adminIndex']);
    Route::put('/installment-plans/{installmentPlan}', [App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'update']);

    // Warranties
    Route::get('/warranties', [App\Http\Controllers\Api\V1\WarrantyController::class, 'adminIndex']);
    Route::get('/warranties/statistics', [App\Http\Controllers\Api\V1\WarrantyController::class, 'statistics']);
    Route::get('/warranties/eligible-orders', [App\Http\Controllers\Api\V1\WarrantyController::class, 'eligibleOrders']);
    Route::post('/warranties/create-for-order/{order}', [App\Http\Controllers\Api\V1\WarrantyController::class, 'createForOrder']);
    Route::put('/warranty-claims/{warrantyClaim}', [App\Http\Controllers\Api\V1\WarrantyController::class, 'updateClaim']);
});