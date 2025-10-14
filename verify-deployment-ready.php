#!/usr/bin/env php
<?php

/**
 * SolaFriq Deployment Readiness Checker
 * 
 * This script verifies that your Laravel application is ready for deployment
 * to a shared hosting environment.
 */

echo "\n";
echo "========================================\n";
echo "SolaFriq Deployment Readiness Check\n";
echo "========================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Root .htaccess
echo "Checking root .htaccess... ";
if (file_exists(__DIR__ . '/.htaccess')) {
    $content = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($content, 'public/$1') !== false) {
        echo "✓ PASS\n";
        $success[] = "Root .htaccess exists and redirects to public/";
    } else {
        echo "⚠ WARNING\n";
        $warnings[] = "Root .htaccess exists but may not have correct redirect rule";
    }
} else {
    echo "✗ FAIL\n";
    $errors[] = "Root .htaccess is missing! This is required for shared hosting.";
}

// Check 2: public/.htaccess
echo "Checking public/.htaccess... ";
if (file_exists(__DIR__ . '/public/.htaccess')) {
    $content = file_get_contents(__DIR__ . '/public/.htaccess');
    if (strpos($content, 'RewriteRule') !== false && strpos($content, 'index.php') !== false) {
        echo "✓ PASS\n";
        $success[] = "public/.htaccess exists with Laravel routing rules";
    } else {
        echo "⚠ WARNING\n";
        $warnings[] = "public/.htaccess exists but may not have correct rewrite rules";
    }
} else {
    echo "✗ FAIL\n";
    $errors[] = "public/.htaccess is missing! Laravel routing will not work.";
}

// Check 3: public/index.php
echo "Checking public/index.php... ";
if (file_exists(__DIR__ . '/public/index.php')) {
    $content = file_get_contents(__DIR__ . '/public/index.php');
    if (strpos($content, 'handleRequest') !== false) {
        echo "✓ PASS\n";
        $success[] = "public/index.php uses modern handleRequest() method";
    } else {
        echo "⚠ WARNING\n";
        $warnings[] = "public/index.php uses older kernel approach (still works, but handleRequest is preferred)";
    }
} else {
    echo "✗ FAIL\n";
    $errors[] = "public/index.php is missing!";
}

// Check 4: Storage directories
echo "Checking storage directories... ";
$requiredDirs = [
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/framework/views',
    'storage/app/public',
    'storage/logs',
];

$missingDirs = [];
foreach ($requiredDirs as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir)) {
        $missingDirs[] = $dir;
    }
}

if (empty($missingDirs)) {
    echo "✓ PASS\n";
    $success[] = "All required storage directories exist";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing storage directories: " . implode(', ', $missingDirs);
}

// Check 5: .env file
echo "Checking .env file... ";
if (file_exists(__DIR__ . '/.env')) {
    echo "✓ PASS\n";
    $success[] = ".env file exists";
    
    // Check critical .env values
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'APP_KEY=base64:') === false || strpos($envContent, 'APP_KEY=') === false) {
        $warnings[] = "APP_KEY may not be set in .env";
    }
} else {
    echo "⚠ WARNING\n";
    $warnings[] = ".env file not found. You'll need to create it on the server.";
}

// Check 6: vendor directory
echo "Checking vendor directory... ";
if (is_dir(__DIR__ . '/vendor') && file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✓ PASS\n";
    $success[] = "Composer dependencies installed";
} else {
    echo "✗ FAIL\n";
    $errors[] = "vendor/ directory missing. Run: composer install";
}

// Check 7: Storage permissions (Unix-like systems only)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "Checking storage permissions... ";
    if (is_writable(__DIR__ . '/storage')) {
        echo "✓ PASS\n";
        $success[] = "storage/ directory is writable";
    } else {
        echo "⚠ WARNING\n";
        $warnings[] = "storage/ directory may not be writable. Run: chmod -R 775 storage";
    }
}

// Check 8: bootstrap/cache permissions (Unix-like systems only)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "Checking bootstrap/cache permissions... ";
    if (is_writable(__DIR__ . '/bootstrap/cache')) {
        echo "✓ PASS\n";
        $success[] = "bootstrap/cache/ directory is writable";
    } else {
        echo "⚠ WARNING\n";
        $warnings[] = "bootstrap/cache/ directory may not be writable. Run: chmod -R 775 bootstrap/cache";
    }
}

// Check 9: PHP version
echo "Checking PHP version... ";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.2.0', '>=')) {
    echo "✓ PASS (PHP $phpVersion)\n";
    $success[] = "PHP version $phpVersion meets requirements (>= 8.2)";
} else {
    echo "✗ FAIL (PHP $phpVersion)\n";
    $errors[] = "PHP version $phpVersion is too old. Requires PHP >= 8.2";
}

// Check 10: Required PHP extensions
echo "Checking required PHP extensions... ";
$requiredExtensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    echo "✓ PASS\n";
    $success[] = "All required PHP extensions are loaded";
} else {
    echo "✗ FAIL\n";
    $errors[] = "Missing PHP extensions: " . implode(', ', $missingExtensions);
}

// Check 11: Production seeder exists
echo "Checking production seeder... ";
if (file_exists(__DIR__ . '/database/seeders/ProductionSeeder.php')) {
    echo "✓ PASS\n";
    $success[] = "ProductionSeeder exists for deployment";
} else {
    echo "⚠ WARNING\n";
    $warnings[] = "ProductionSeeder not found. You may need to create it for production deployment.";
}

// Summary
echo "\n========================================\n";
echo "Summary\n";
echo "========================================\n\n";

if (!empty($success)) {
    echo "✓ PASSED (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠ WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  • $msg\n";
    }
    echo "\n";
}

// Final verdict
echo "========================================\n";
if (empty($errors)) {
    if (empty($warnings)) {
        echo "✅ READY FOR DEPLOYMENT!\n";
        echo "========================================\n\n";
        echo "Next steps:\n";
        echo "1. Upload all files to your shared hosting\n";
        echo "2. Update .env with production settings\n";
        echo "3. Run: php artisan migrate --force\n";
        echo "4. Run: php artisan storage:link\n";
        echo "5. Run: php artisan optimize\n";
        echo "\nSee SHARED-HOSTING-SETUP.md for detailed instructions.\n\n";
        exit(0);
    } else {
        echo "⚠️  READY WITH WARNINGS\n";
        echo "========================================\n\n";
        echo "You can deploy, but address the warnings above.\n";
        echo "See SHARED-HOSTING-SETUP.md for help.\n\n";
        exit(0);
    }
} else {
    echo "❌ NOT READY FOR DEPLOYMENT\n";
    echo "========================================\n\n";
    echo "Fix the errors above before deploying.\n";
    echo "See DEPLOYMENT-FIX.md for solutions.\n\n";
    exit(1);
}
