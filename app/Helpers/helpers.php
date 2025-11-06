<?php

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Storage;

/**
 * Get a company setting value by key
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        return CompanySetting::get($key, $default);
    }
}

/**
 * Get current storage disk
 *
 * @return string
 */
if (!function_exists('getDisk')) {
    function getDisk(): string
    {
        return config('filesystems.default', 'local');
    }
}

/**
 * Check if mail credentials are configured
 *
 * @return bool
 */
if (!function_exists('checkMailCreds')) {
    function checkMailCreds(): bool
    {
        // Don't send in demo mode
        if (config('app.demo_mode', false)) {
            return false;
        }

        // Check if mail is configured in .env
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');
        $mailUsername = config('mail.mailers.smtp.username');
        $mailPassword = config('mail.mailers.smtp.password');
        $mailFromAddress = config('mail.from.address');
        $mailFromName = config('mail.from.name');

        if (
            !empty($mailHost) &&
            !empty($mailPort) &&
            !empty($mailUsername) &&
            !empty($mailPassword) &&
            !empty($mailFromAddress) &&
            !empty($mailFromName)
        ) {
            return true;
        }

        return false;
    }
}

/**
 * Get asset URL with proper path handling
 *
 * @param string $path
 * @return string
 */
if (!function_exists('assetUrl')) {
    function assetUrl(string $path): string
    {
        // If it's already a full URL, return as is
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // If it starts with /storage/, return as is
        if (str_starts_with($path, '/storage/')) {
            return url($path);
        }

        // If it starts with /, it's a public path
        if (str_starts_with($path, '/')) {
            return url($path);
        }

        // Otherwise, it's a storage path
        return Storage::url($path);
    }
}

/**
 * Format currency with symbol
 *
 * @param float $amount
 * @param string|null $currency
 * @return string
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount, ?string $currency = null): string
    {
        $symbol = $currency ?? setting('currency_symbol', '$');
        return $symbol . number_format($amount, 2);
    }
}

/**
 * Get company name
 *
 * @return string
 */
if (!function_exists('companyName')) {
    function companyName(): string
    {
        return setting('company_name', config('app.name', 'SolaFriq'));
    }
}

/**
 * Get company email
 *
 * @return string
 */
if (!function_exists('companyEmail')) {
    function companyEmail(): string
    {
        return setting('company_email', 'info@solafriq.com');
    }
}

/**
 * Get company phone
 *
 * @return string
 */
if (!function_exists('companyPhone')) {
    function companyPhone(): string
    {
        return setting('company_phone', '+1-XXX-XXX-XXXX');
    }
}

/**
 * Get company logo URL
 *
 * @return string
 */
if (!function_exists('companyLogo')) {
    function companyLogo(): string
    {
        $logo = setting('company_logo', '/images/solafriq-logo.svg');
        return assetUrl($logo);
    }
}

/**
 * Log activity with context
 *
 * @param string $message
 * @param array $context
 * @return void
 */
if (!function_exists('logActivity')) {
    function logActivity(string $message, array $context = []): void
    {
        \Log::info($message, array_merge($context, [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }
}

/**
 * Success redirect with message
 *
 * @param string $message
 * @param string|null $route
 * @return \Illuminate\Http\RedirectResponse
 */
if (!function_exists('successRedirect')) {
    function successRedirect(string $message, ?string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with([
            'message' => $message,
            'alert-type' => 'success',
        ]);
    }
}

/**
 * Error redirect with message
 *
 * @param string $message
 * @param string|null $route
 * @return \Illuminate\Http\RedirectResponse
 */
if (!function_exists('errorRedirect')) {
    function errorRedirect(string $message, ?string $route = null)
    {
        $redirect = $route ? redirect()->route($route) : back();
        
        return $redirect->with([
            'message' => $message,
            'alert-type' => 'error',
        ]);
    }
}

/**
 * Format date for display
 *
 * @param string|\Carbon\Carbon $date
 * @param string $format
 * @return string
 */
if (!function_exists('formatDate')) {
    function formatDate($date, string $format = 'M d, Y'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }
}

/**
 * Format datetime for display
 *
 * @param string|\Carbon\Carbon $datetime
 * @param string $format
 * @return string
 */
if (!function_exists('formatDateTime')) {
    function formatDateTime($datetime, string $format = 'M d, Y h:i A'): string
    {
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }
        
        return $datetime->format($format);
    }
}

/**
 * Check if user has role
 *
 * @param string $role
 * @return bool
 */
if (!function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        return auth()->check() && auth()->user()->role === $role;
    }
}

/**
 * Check if user is admin
 *
 * @return bool
 */
if (!function_exists('isAdmin')) {
    function isAdmin(): bool
    {
        return hasRole('admin');
    }
}

/**
 * Generate unique order number
 *
 * @return string
 */
if (!function_exists('generateOrderNumber')) {
    function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}

/**
 * Generate unique tracking number
 *
 * @param int|null $orderId Optional order ID for better tracking number generation
 * @return string
 */
if (!function_exists('generateTrackingNumber')) {
    function generateTrackingNumber(?int $orderId = null): string
    {
        $prefix = config('solafriq.tracking_prefix', 'SF');

        if ($orderId) {
            // Format: SF20250106000001 (Prefix + Date + OrderID)
            return $prefix . date('Ymd') . str_pad($orderId, 6, '0', STR_PAD_LEFT);
        }

        // Format: SF2025012345 (Prefix + Year + Random) - ensure uniqueness
        do {
            $trackingNumber = $prefix . date('Y') . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (\App\Models\Order::where('tracking_number', $trackingNumber)->exists());

        return $trackingNumber;
    }
}

/**
 * Calculate tax amount
 *
 * @param float $amount
 * @param float|null $taxRate
 * @return float
 */
if (!function_exists('calculateTax')) {
    function calculateTax(float $amount, ?float $taxRate = null): float
    {
        $rate = $taxRate ?? (float) setting('tax_rate', 0);
        return round($amount * ($rate / 100), 2);
    }
}

/**
 * Calculate total with tax
 *
 * @param float $subtotal
 * @param float|null $taxRate
 * @return float
 */
if (!function_exists('calculateTotalWithTax')) {
    function calculateTotalWithTax(float $subtotal, ?float $taxRate = null): float
    {
        $tax = calculateTax($subtotal, $taxRate);
        return round($subtotal + $tax, 2);
    }
}
