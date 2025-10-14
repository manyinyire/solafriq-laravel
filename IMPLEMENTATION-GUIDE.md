# Implementation Guide - Eventmie-Pro Best Practices

This guide documents all the improvements implemented from the Eventmie-Pro comparison.

## 🎯 What Was Implemented

### ✅ Critical Features (Completed)

1. **Image Optimization Service** (`app/Services/ImageOptimizationService.php`)
   - Automatic WebP conversion for all uploads
   - Aspect ratio preservation
   - Multiple upload methods (avatar, logo, product images)
   - S3 and local storage support
   - Automatic thumbnail generation

2. **Helper Functions** (`app/Helpers/helpers.php`)
   - `setting()` - Get company settings easily
   - `getDisk()` - Get current storage disk
   - `checkMailCreds()` - Verify mail configuration
   - `assetUrl()` - Handle asset URLs properly
   - `formatCurrency()` - Format currency with symbol
   - `companyName()`, `companyEmail()`, `companyPhone()`, `companyLogo()` - Quick access to company info
   - `successRedirect()`, `errorRedirect()` - Consistent redirect messages
   - `formatDate()`, `formatDateTime()` - Date formatting
   - `calculateTax()`, `calculateTotalWithTax()` - Tax calculations
   - And many more utility functions

3. **Enhanced Settings System**
   - Added `group`, `display_name`, and `order` fields to settings
   - Organized settings into groups (Company, Financial, Product)
   - Better UI organization and display

4. **Avatar Upload System**
   - Profile picture upload with optimization
   - Automatic old avatar deletion
   - WebP conversion for avatars

5. **Welcome Email Notifications**
   - `WelcomeNotification` - Sent to new users
   - `NewUserRegisteredNotification` - Sent to admins
   - Graceful fallback to database-only if mail not configured

6. **Configuration Files**
   - `config/logging.php` - Multiple log channels (daily, slack, papertrail)
   - `config/mail.php` - Mail configuration with multiple mailers
   - `config/filesystems.php` - Storage configuration with S3 support
   - `config/cache.php` - Cache configuration
   - `config/queue.php` - Queue configuration
   - `config/session.php` - Session configuration

---

## 📦 Installation Steps

### Step 1: Install Dependencies

```bash
# Install Intervention Image for image optimization
composer require intervention/image

# Run composer dump-autoload to load helpers
composer dump-autoload
```

### Step 2: Run Migrations

```bash
# Add new fields to company_settings table
php artisan migrate
```

### Step 3: Update Environment Variables

Add these to your `.env` file:

```env
# Storage Configuration
FILESYSTEM_DISK=local
# Change to 's3' for AWS S3 storage

# Cache Configuration
CACHE_STORE=database
CACHE_PREFIX=solafriq_cache_

# Queue Configuration
QUEUE_CONNECTION=database

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Logging Configuration
LOG_CHANNEL=daily
LOG_LEVEL=debug
LOG_DAILY_DAYS=14

# Mail Configuration (for production)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@solafriq.com
MAIL_FROM_NAME="${APP_NAME}"

# AWS S3 Configuration (if using S3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Step 4: Create Storage Link

```bash
# Create symbolic link for public storage
php artisan storage:link
```

### Step 5: Initialize Settings

```bash
# Run this in tinker or create a command
php artisan tinker

# Then run:
App\Models\CompanySetting::initializeDefaults();
```

### Step 6: Create Required Database Tables

Make sure you have these migrations:

```bash
# Create cache table
php artisan cache:table

# Create queue tables
php artisan queue:table
php artisan queue:failed-table

# Create session table
php artisan session:table

# Run all migrations
php artisan migrate
```

---

## 🎨 Usage Examples

### Using the Image Optimization Service

```php
use App\Services\ImageOptimizationService;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageOptimizationService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function store(Request $request)
    {
        // Upload and optimize product image
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->uploadProductImage(
                $request->file('image')
            );
            
            // Create thumbnail
            $thumbnailPath = $this->imageService->createThumbnail($imagePath);
        }
    }
}
```

### Using Helper Functions

```php
// Get company settings
$companyName = companyName(); // Returns "SolaFriq"
$logo = companyLogo(); // Returns full URL to logo

// Get any setting
$taxRate = setting('tax_rate', 0); // Returns 8.25 or default 0

// Format currency
$price = formatCurrency(1500.50); // Returns "$1,500.50"

// Calculate tax
$tax = calculateTax(1000); // Returns 82.50 (based on 8.25% rate)
$total = calculateTotalWithTax(1000); // Returns 1082.50

// Check storage disk
if (getDisk() === 's3') {
    // Using S3 storage
}

// Redirect with messages
return successRedirect('Order created successfully!', 'orders.index');
return errorRedirect('Failed to create order');
```

### Sending Notifications

```php
use App\Notifications\WelcomeNotification;
use App\Notifications\NewUserRegisteredNotification;

// Send welcome email to new user
$user->notify(new WelcomeNotification());

// Notify all admins about new user
$admins = User::where('role', 'ADMIN')->get();
Notification::send($admins, new NewUserRegisteredNotification($user));
```

### Working with Settings

```php
// Get all settings
$settings = CompanySetting::getAll();

// Get public settings (for frontend)
$publicSettings = CompanySetting::getPublic();

// Set a setting
CompanySetting::set(
    'company_name',
    'SolaFriq Energy',
    'string',
    true, // is_public
    'Company name displayed throughout the application',
    'Company', // group
    'Company Name', // display_name
    1 // order
);

// Get a specific setting
$logo = CompanySetting::get('company_logo');
```

---

## 🔧 Configuration Details

### Image Optimization Settings

The `ImageOptimizationService` uses these default settings:

- **Avatars**: 512x512px, 90% quality, WebP format
- **Logos**: 480x270px, 90% quality, WebP format
- **Product Images**: 1200x1200px, 85% quality, WebP format
- **Thumbnails**: 150x150px, 85% quality, WebP format

All images maintain aspect ratio and won't upsize smaller images.

### Storage Structure

Images are organized by type and date:

```
storage/app/public/
├── avatars/
│   └── 2025/January/
│       └── 1234567890_abc123.webp
├── logos/
│   └── 2025/January/
│       └── 1234567891_def456.webp
└── products/
    └── 2025/January/
        └── 1234567892_ghi789.webp
```

### Mail Fallback System

The notification system automatically falls back to database-only notifications if mail is not configured:

```php
public function via(object $notifiable): array
{
    // Check if mail is configured
    if (checkMailCreds()) {
        return ['mail', 'database'];
    }
    
    // Fallback to database only
    return ['database'];
}
```

---

## 🚀 Running the Application

### Development Mode

```bash
# Start the development server
php artisan serve

# In another terminal, run the queue worker
php artisan queue:work

# In another terminal, watch for file changes (if using Vite)
npm run dev
```

### Production Mode

```bash
# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run queue worker as a daemon
php artisan queue:work --daemon

# Build frontend assets
npm run build
```

---

## 📝 Testing

### Test Image Upload

```php
use App\Services\ImageOptimizationService;

$service = new ImageOptimizationService();

// Test avatar upload
$file = $request->file('avatar');
$path = $service->uploadAvatar($file);
// Returns: avatars/2025/January/1234567890_abc123.webp

// Test deletion
$service->deleteImage($path);
```

### Test Helper Functions

```php
// Test in tinker
php artisan tinker

// Test setting helper
setting('company_name'); // Should return "SolaFriq"

// Test mail check
checkMailCreds(); // Should return true/false

// Test currency formatting
formatCurrency(1500.50); // Should return "$1,500.50"
```

### Test Notifications

```php
// Test in tinker
php artisan tinker

$user = User::first();
$user->notify(new App\Notifications\WelcomeNotification());

// Check database
DB::table('notifications')->where('notifiable_id', $user->id)->get();
```

---

## 🐛 Troubleshooting

### Images Not Uploading

1. Check storage permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

2. Ensure storage link exists:
```bash
php artisan storage:link
```

3. Check `FILESYSTEM_DISK` in `.env`

### Helpers Not Working

1. Run composer dump-autoload:
```bash
composer dump-autoload
```

2. Clear config cache:
```bash
php artisan config:clear
```

### Notifications Not Sending

1. Check mail configuration:
```bash
php artisan tinker
checkMailCreds(); // Should return true
```

2. Check queue is running:
```bash
php artisan queue:work
```

3. Check failed jobs:
```bash
php artisan queue:failed
```

### Settings Not Saving

1. Clear cache:
```bash
php artisan cache:clear
```

2. Check database connection

3. Verify migrations ran:
```bash
php artisan migrate:status
```

---

## 📚 Additional Resources

### Intervention Image Documentation
https://image.intervention.io/v3

### Laravel Notifications
https://laravel.com/docs/notifications

### Laravel Queue
https://laravel.com/docs/queues

### Laravel Storage
https://laravel.com/docs/filesystem

---

## ✨ What's Next?

Consider implementing these additional features:

1. **Admin UI** - Create admin interface for managing settings
2. **Localization** - Add multi-language support
3. **Advanced Caching** - Implement Redis for better performance
4. **Image Variants** - Generate multiple sizes for responsive images
5. **Audit Logging** - Track all setting changes
6. **Backup System** - Automated backups of settings and uploads

---

## 📞 Support

If you encounter any issues:

1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check queue failed jobs: `php artisan queue:failed`
4. Verify environment configuration

---

**Implementation completed successfully! 🎉**

All critical features from Eventmie-Pro have been implemented and are ready to use.
