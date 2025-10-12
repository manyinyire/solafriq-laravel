#!/bin/bash

# Laravel Deployment Script for Shared Hosting
# This script helps prepare your application for deployment

echo "🚀 Starting Laravel Deployment Preparation..."

# Step 1: Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Step 2: Install production dependencies
echo "📚 Installing production dependencies..."
composer install --optimize-autoloader --no-dev

# Step 3: Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# Step 4: Optimize for production
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment preparation complete!"
echo ""
echo "📝 Next steps:"
echo "1. Create a ZIP file of your project (exclude node_modules, .git, .env)"
echo "2. Upload to your shared hosting"
echo "3. Extract in a folder OUTSIDE public_html (e.g., laravel_app)"
echo "4. Move/symlink the 'public' folder contents to public_html"
echo "5. Create .env file with production settings"
echo "6. Run: php artisan key:generate"
echo "7. Run: php artisan migrate --force"
echo "8. Set permissions: chmod -R 755 storage bootstrap/cache"
