<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    $solarSystems = App\Models\SolarSystem::active()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'solarSystems' => $solarSystems,
    ]);
});

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/services', function () {
    return Inertia::render('Services');
})->name('services');

// Solar System Routes
Route::get('/systems', [App\Http\Controllers\SolarSystemController::class, 'index'])->name('systems.index');
Route::get('/systems/{id}', [App\Http\Controllers\SolarSystemController::class, 'show'])->name('systems.show');

// Cart Routes
Route::get('/cart', [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{cartItem}', [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/process', [App\Http\Controllers\CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/success/{order}', [App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

// Authentication Routes
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'webLogin'])->name('login.post');
Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'webRegister'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\Auth\AuthController::class, 'webLogout'])->name('logout');

// Email Verification
Route::get('/email/verify', [App\Http\Controllers\Auth\AuthController::class, 'verificationNotice'])
    ->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\AuthController::class, 'webVerifyEmail'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', [App\Http\Controllers\Auth\AuthController::class, 'webResendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Custom system builder (public)
Route::post('custom-builder/calculate', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'calculate']);
Route::post('custom-builder/validate', [App\Http\Controllers\Api\V1\CustomBuilderController::class, 'validateSystem']);
Route::get('products/category/{category}', [App\Http\Controllers\Admin\ProductController::class, 'byCategory']);

Route::middleware([
    'auth', 'verified'// Changed from auth:sanctum
])->group(function () {
    // User Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Client Dashboard APIs
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\V1\DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-orders', [\App\Http\Controllers\Api\V1\DashboardController::class, 'recentOrders']);
    Route::get('/dashboard/installment-summary', [\App\Http\Controllers\Api\V1\DashboardController::class, 'installmentSummary']);
    Route::get('/dashboard/warranty-summary', [\App\Http\Controllers\Api\V1\DashboardController::class, 'warrantySummary']);

    // Client Pages
    Route::get('/orders', function () {
        return Inertia::render('Client/Orders');
    })->name('client.orders');

    Route::get('/orders-data', [\App\Http\Controllers\Api\V1\OrderController::class, 'index'])->name('client.orders.data');

    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('client.orders.show');

    Route::get('/invoices', function () {
        return Inertia::render('Client/Invoices');
    })->name('client.invoices');

    Route::get('/invoices/{invoice}', function ($invoice) {
        return Inertia::render('Client/InvoiceDetails', ['invoiceId' => $invoice]);
    })->name('client.invoices.show');

    Route::get('/installments', function () {
        return Inertia::render('Client/Installments');
    })->name('client.installments');

    Route::get('/warranties', function () {
        return Inertia::render('Client/Warranties');
    })->name('client.warranties');

    Route::get('/profile', function () {
        return Inertia::render('Profile/Show');
    })->name('profile.show');

    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/profile/verify-email-change/{token}', [App\Http\Controllers\ProfileController::class, 'verifyEmailChange'])->name('profile.verifyEmailChange')->middleware('signed');

    // Installment Plans
    Route::apiResource('installment-plans', \App\Http\Controllers\Api\V1\InstallmentPlanController::class);
    Route::post('installment-plans/{installmentPlan}/payments/{payment}/pay', [\App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'processPayment']);

    // Warranties
    Route::get('warranties', [\App\Http\Controllers\Api\V1\WarrantyController::class, 'index']);
    Route::get('warranties/{warranty}', [\App\Http\Controllers\Api\V1\WarrantyController::class, 'show']);
    Route::post('warranties/{warranty}/claims', [\App\Http\Controllers\Api\V1\WarrantyController::class, 'createClaim']);
    Route::get('warranty-claims', [\App\Http\Controllers\Api\V1\WarrantyController::class, 'claims']);

    // Custom System Builder (authenticated)
    Route::post('custom-builder/save', [\App\Http\Controllers\Api\V1\CustomBuilderController::class, 'save']);
    Route::get('custom-builder/saved', [\App\Http\Controllers\Api\V1\CustomBuilderController::class, 'saved']);

    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');

        // Admin Dashboard API
        Route::get('/dashboard/overview', [\App\Http\Controllers\Api\V1\DashboardController::class, 'adminOverview']);
        Route::get('/dashboard/sales-analytics', [\App\Http\Controllers\Api\V1\DashboardController::class, 'salesAnalytics']);
        Route::get('/dashboard/system-metrics', [\App\Http\Controllers\Api\V1\DashboardController::class, 'systemMetrics']);
        Route::get('/installment-plans', [\App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'adminIndex']);
        Route::put('/installment-plans/{installmentPlan}', [\App\Http\Controllers\Api\V1\InstallmentPlanController::class, 'update']);

        // Company Settings Page
        Route::get('/settings', function () {
            return Inertia::render('Admin/CompanySettings');
        })->name('admin.settings');
        
        // Company Settings APIs
        Route::get('/settings/data', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'index'])->name('admin.settings.data');
        Route::put('/settings/data', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'update'])->name('admin.settings.update');
        Route::post('/settings/reset', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'resetToDefaults'])->name('admin.settings.reset');
        Route::get('/settings/export', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'export'])->name('admin.settings.export');
        Route::post('/settings/import', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'import'])->name('admin.settings.import');

        Route::get('/users', function () {
            return Inertia::render('Admin/Users');
        })->name('admin.users');

        Route::get('/users-data', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.data');
        Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('admin.users.show');
        Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/bulk', [App\Http\Controllers\Admin\UserController::class, 'bulkAction'])->name('admin.users.bulk');

        Route::get('/systems', [App\Http\Controllers\Admin\SolarSystemController::class, 'index'])->name('admin.systems');
        Route::get('/systems/{id}', [App\Http\Controllers\Admin\SolarSystemController::class, 'show'])->name('admin.systems.show');
        Route::post('/systems', [App\Http\Controllers\Admin\SolarSystemController::class, 'store'])->name('admin.systems.store');
        Route::put('/systems/{id}', [App\Http\Controllers\Admin\SolarSystemController::class, 'update'])->name('admin.systems.update');
        Route::delete('/systems/{id}', [App\Http\Controllers\Admin\SolarSystemController::class, 'destroy'])->name('admin.systems.destroy');

        Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('admin.products');
        Route::get('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('admin.products.show');
        Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('admin.products.store');
        Route::put('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{id}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('admin.products.destroy');

        Route::get('/orders', function () {
            return Inertia::render('Admin/Orders');
        })->name('admin.orders');

        Route::get('/orders/{order}', function ($order) {
            return Inertia::render('Admin/OrderDetails', ['orderId' => $order]);
        })->name('admin.orders.show');

        Route::get('/orders/{order}/data', [\App\Http\Controllers\Api\V1\OrderController::class, 'show'])->name('admin.orders.show.data');

        Route::get('/orders-data', [\App\Http\Controllers\Api\V1\OrderController::class, 'index'])->name('admin.orders.data');

        Route::put('/orders/{order}', [\App\Http\Controllers\Api\V1\OrderController::class, 'update']);

        Route::put('/orders/{order}/accept', [\App\Http\Controllers\Api\V1\OrderController::class, 'accept'])->name('admin.orders.accept');
        Route::put('/orders/{order}/decline', [\App\Http\Controllers\Api\V1\OrderController::class, 'decline'])->name('admin.orders.decline');

        Route::put('/orders/{order}/confirm-payment', [\App\Http\Controllers\Api\V1\OrderController::class, 'confirmPayment'])->name('admin.orders.confirm-payment');
        Route::put('/orders/{order}/status', [\App\Http\Controllers\Api\V1\OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
        Route::put('/orders/{order}/schedule-installation', [\App\Http\Controllers\Api\V1\OrderController::class, 'scheduleInstallation'])->name('admin.orders.schedule-installation');
        Route::put('/orders/{order}/tracking', [\App\Http\Controllers\Api\V1\OrderController::class, 'updateTracking'])->name('admin.orders.update-tracking');
        Route::post('/orders/{order}/notes', [\App\Http\Controllers\Api\V1\OrderController::class, 'addNote'])->name('admin.orders.add-note');
        Route::post('/orders/{order}/refund', [\App\Http\Controllers\Api\V1\OrderController::class, 'refund'])->name('admin.orders.refund');
        Route::post('/orders/{order}/resend-notification', [\App\Http\Controllers\Api\V1\OrderController::class, 'resendNotification'])->name('admin.orders.resend-notification');
        Route::get('/orders/{order}/invoice-pdf', [\App\Http\Controllers\Api\V1\OrderController::class, 'downloadInvoice'])->name('admin.orders.invoice-pdf');

        Route::get('/analytics', function () {
            return Inertia::render('Admin/Analytics');
        })->name('admin.analytics');

        Route::get('/warranties', function () {
            return Inertia::render('Admin/Warranties');
        })->name('admin.warranties');
    });
});

// Public company settings (for frontend use)
Route::get('/public/settings', [\App\Http\Controllers\Admin\CompanySettingsController::class, 'getPublic']);

