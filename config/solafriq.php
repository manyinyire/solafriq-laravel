<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    |
    | Configure tax rates and tax-related settings for the application.
    |
    */
    'tax_rate' => env('TAX_RATE', 0.0825), // 8.25% default sales tax

    /*
    |--------------------------------------------------------------------------
    | Shipping Configuration
    |--------------------------------------------------------------------------
    |
    | Configure shipping and delivery-related settings.
    |
    */
    'estimated_delivery_days' => env('ESTIMATED_DELIVERY_DAYS', 3),
    'default_carrier' => env('DEFAULT_CARRIER', 'SolaFriq Logistics'),

    /*
    |--------------------------------------------------------------------------
    | Contact Configuration
    |--------------------------------------------------------------------------
    |
    | Configure contact emails and phone numbers for different purposes.
    |
    */
    'admin_emails' => explode(',', env('ADMIN_EMAILS', 'admin@solafriq.com,orders@solafriq.com')),
    'support_email' => env('SUPPORT_EMAIL', 'support@solafriq.com'),
    'support_phone' => env('SUPPORT_PHONE', '+1-800-555-0123'),

    /*
    |--------------------------------------------------------------------------
    | Order Configuration
    |--------------------------------------------------------------------------
    |
    | Configure order-related prefixes and settings.
    |
    */
    'order_prefix' => env('ORDER_PREFIX', 'ORD'),
    'tracking_prefix' => env('TRACKING_PREFIX', 'SF'),
    'warranty_prefix' => env('WARRANTY_PREFIX', 'WR'),

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configure payment terms and related settings.
    |
    */
    'payment_terms_days' => env('PAYMENT_TERMS_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Inventory Configuration
    |--------------------------------------------------------------------------
    |
    | Configure inventory management settings.
    |
    */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for the application.
    |
    */
    'default_per_page' => env('DEFAULT_PER_PAGE', 15),
];
