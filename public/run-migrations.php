<?php
/**
 * TEMPORARY MIGRATION RUNNER
 * Upload this to public_html/ and visit: https://www.solafriq.com/run-migrations.php?key=YOUR_SECRET_KEY
 * DELETE THIS FILE IMMEDIATELY AFTER USE!
 */

// Set your secret key
define('SECRET_KEY', 'change-this-to-something-secure-' . date('Ymd'));

// Check authorization
if (!isset($_GET['key']) || $_GET['key'] !== SECRET_KEY) {
    die('Unauthorized. Set the correct key parameter.');
}

// Set paths based on your setup
// If using symbolic link (public_html -> app/public)
$basePath = __DIR__ . '/..';

// If using copied files (uncomment the line below instead)
// $basePath = __DIR__ . '/../app';

// Load Laravel
require $basePath . '/vendor/autoload.php';
$app = require_once $basePath . '/bootstrap/app.php';

// Set environment
$app->make(\Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";
echo "Running Database Migrations...\n";
echo "================================\n\n";

try {
    // Run migrations
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo \Illuminate\Support\Facades\Artisan::output();
    
    echo "\n================================\n";
    echo "Migrations completed successfully!\n";
    echo "\n⚠️  DELETE THIS FILE NOW!\n";
    echo "================================\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
}

echo "</pre>";
?>
