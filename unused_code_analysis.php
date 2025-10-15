<?php

/**
 * Unused Code Analysis Script
 * This script identifies potentially unused controllers, methods, models, and APIs
 */

// Controllers and their methods used in routes
$usedControllers = [
    // Web Controllers
    'App\Http\Controllers\SolarSystemController' => ['index', 'show'],
    'App\Http\Controllers\CartController' => ['index', 'add', 'update', 'remove', 'clear'],
    'App\Http\Controllers\CheckoutController' => ['index', 'process'],
    'App\Http\Controllers\QuoteController' => ['requestQuote', 'index', 'show', 'accept', 'reject'],
    'App\Http\Controllers\Auth\AuthController' => [
        'webLogin', 'webRegister', 'webLogout',
        'verificationNotice', 'webVerifyEmail', 'webResendVerificationEmail'
    ],
    'App\Http\Controllers\OrderController' => ['show'],
    'App\Http\Controllers\ProfileController' => ['update', 'verifyEmailChange'],
    
    // API Controllers
    'App\Http\Controllers\Api\V1\CustomBuilderController' => [
        'getProducts', 'calculate', 'validateSystem', 'addToCart', 'save', 'saved'
    ],
    'App\Http\Controllers\Api\V1\DashboardController' => [
        'stats', 'recentOrders', 'installmentSummary', 'warrantySummary',
        'adminOverview', 'salesAnalytics', 'systemMetrics'
    ],
    'App\Http\Controllers\Api\V1\OrderController' => [
        'index', 'show', 'update', 'accept', 'decline',
        'confirmPayment', 'updateStatus', 'scheduleInstallation',
        'updateTracking', 'addNote', 'refund', 'resendNotification',
        'downloadInvoice', 'scheduledInstallations'
    ],
    'App\Http\Controllers\Api\V1\InstallmentPlanController' => [
        'index', 'store', 'show', 'update', 'destroy',
        'processPayment', 'adminIndex'
    ],
    'App\Http\Controllers\Api\V1\WarrantyController' => [
        'index', 'show', 'downloadCertificate', 'createClaim', 'claims',
        'adminIndex', 'statistics', 'eligibleOrders', 'createForOrder', 'updateClaim'
    ],
    
    // Admin Controllers
    'App\Http\Controllers\Admin\CompanySettingsController' => [
        'index', 'update', 'resetToDefaults', 'export', 'import', 'getPublic'
    ],
    'App\Http\Controllers\Admin\UserController' => [
        'index', 'show', 'update', 'destroy', 'bulkAction'
    ],
    'App\Http\Controllers\Admin\SolarSystemController' => [
        'index', 'show', 'store', 'update', 'destroy'
    ],
    'App\Http\Controllers\Admin\ProductController' => [
        'index', 'show', 'store', 'update', 'destroy', 'exportCsv'
    ],
    'App\Http\Controllers\Admin\QuoteController' => [
        'index', 'show', 'update', 'updateItems', 'send', 'downloadPDF', 'destroy'
    ],
];

// Potentially unused API methods in AuthController (not in web routes)
$unusedAuthMethods = [
    'register',           // API version (webRegister is used)
    'login',             // API version (webLogin is used)
    'logout',            // API version (webLogout is used)
    'user',              // Might be used via /api/user route
    'updateProfile',     // Not in routes
    'changePassword',    // Not in routes
    'forgotPassword',    // Not in routes
    'resetPassword',     // Not in routes
    'verifyEmail',       // API version (webVerifyEmail is used)
    'resendVerificationEmail', // API version (webResendVerificationEmail is used)
    'deleteAccount',     // Not in routes
];

// Methods to check in CheckoutController
$unusedCheckoutMethods = [
    'success', // Not in routes
];

echo "=== UNUSED CODE ANALYSIS ===\n\n";

echo "## Potentially Unused API Methods in AuthController:\n";
foreach ($unusedAuthMethods as $method) {
    echo "  - AuthController::$method()\n";
}

echo "\n## Potentially Unused Methods in CheckoutController:\n";
foreach ($unusedCheckoutMethods as $method) {
    echo "  - CheckoutController::$method()\n";
}

echo "\n## Analysis Notes:\n";
echo "1. The api.php routes file only has one route: GET /api/user\n";
echo "2. All other API endpoints are in web.php, not api.php\n";
echo "3. AuthController has many API methods (register, login, logout, etc.) that may be unused\n";
echo "4. CheckoutController::success() method is not referenced in routes\n";
echo "5. No API routes found for:\n";
echo "   - Password reset functionality\n";
echo "   - Account deletion\n";
echo "   - Profile update via API\n";
echo "   - Change password via API\n";

echo "\n## Recommendations:\n";
echo "1. Remove unused API methods from AuthController if not needed\n";
echo "2. Remove CheckoutController::success() if not used\n";
echo "3. Consider moving API endpoints from web.php to api.php\n";
echo "4. Add proper API routes if the methods are intended to be used\n";
echo "5. Check for unused Events (OrderCreated, OrderUpdated)\n";
echo "6. Check for unused Commands (GenerateMissingInvoices)\n";
echo "7. Review unused Request classes\n";

echo "\n=== END OF ANALYSIS ===\n";
