# Production Deployment Guide

## 🚀 Pre-Deployment Checklist

### 1. Code is Ready ✅
- All changes committed
- No linter errors
- Tested locally

### 2. Files to Deploy
- All updated PHP files
- All updated Vue.js files  
- New migrations
- Built frontend assets

### 3. Database Backup (CRITICAL!)
```bash
# Backup production database before migration
mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

## 📦 Deployment Steps

### Step 1: Pull Latest Code
```bash
cd /path/to/production
git pull origin main
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies
npm ci --production
```

### Step 3: Clear All Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 4: Run Migrations
```bash
# Run pending migrations
php artisan migrate --force

# This will:
# 1. Add 'custom_component' to order_items type enum
# 2. Make user_id nullable in warranties table
# 3. Add 'converted' status to quotes table
# 4. Fix orders status enum (removes CONFIRMED if present)
```

### Step 5: Build Frontend
```bash
npm run build
```

### Step 6: Cache for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 7: Set Permissions
```bash
# Set storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Set public/uploads permissions
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

### Step 8: Restart Services (if needed)
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
# or
sudo service php8.2-fpm restart

# If using queue workers
php artisan queue:restart
```

## ⚠️ Important Notes

### New Migrations
The following migrations will run:
1. `2025_10_27_072754_add_custom_component_to_order_items_type_enum`
2. `2025_10_27_074911_make_user_id_nullable_in_warranties_table`
3. `2025_10_27_115308_add_converted_status_to_quotes_table`
4. `2025_10_27_140000_fix_orders_status_enum_for_production` ⭐ **NEW**

### The `fix_orders_status_enum` Migration
This migration will:
- Find any orders with invalid status values (like `'CONFIRMED'`)
- Update them to `'PENDING'` 
- Ensure the enum only contains valid values:
  - `PENDING`
  - `PROCESSING`
  - `SCHEDULED`
  - `INSTALLED`
  - `SHIPPED`
  - `DELIVERED`
  - `CANCELLED`
  - `RETURNED`
  - `REFUNDED`

### Testing After Deployment
1. ✅ Test quote acceptance (admin and customer)
2. ✅ Verify order creation from quotes
3. ✅ Check invoice generation
4. ✅ Verify dashboard displays correctly
5. ✅ Test warranty creation

## 🔧 Rollback Plan

If something goes wrong:

```bash
# Rollback last batch of migrations
php artisan migrate:rollback

# Or rollback specific migration
php artisan migrate:rollback --step=1

# Restore database backup
mysql -u username -p database_name < backup_20251027_HHMMSS.sql

# Revert code
git reset --hard HEAD~1
```

## 📊 Post-Deployment Verification

### Check Migration Status
```bash
php artisan migrate:status
```

### Check for Errors
```bash
tail -f storage/logs/laravel.log
```

### Verify Quote Features
1. Go to `/admin/quotes`
2. Open a quote
3. Click "Accept on Behalf" 
4. Verify it converts to order
5. Check order status is `PENDING` (not `CONFIRMED`)

## 🎯 Expected Results

After deployment:
- ✅ Quote acceptance works without errors
- ✅ Orders are created with valid status (`PENDING`)
- ✅ Invoices are generated automatically
- ✅ Quotes show status as `converted`
- ✅ Dashboard displays correctly
- ✅ No more "CONFIRMED" status errors

## 🔍 Troubleshooting

### Issue: Migration fails with enum error
```bash
# Check current enum values
mysql -u username -p database_name -e "SHOW COLUMNS FROM orders LIKE 'status'"

# Manual fix if needed
mysql -u username -p database_name
ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING','PROCESSING','SCHEDULED','INSTALLED','SHIPPED','DELIVERED','CANCELLED','RETURNED','REFUNDED') DEFAULT 'PENDING';
```

### Issue: Quote acceptance still fails
1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify migration ran: `php artisan migrate:status`
3. Clear cache: `php artisan cache:clear`

## 📝 Summary

**New Migration Added:** `2025_10_27_140000_fix_orders_status_enum_for_production.php`

**What it does:**
- Removes any invalid status values from orders
- Ensures orders.status enum only contains valid values
- Prevents "CONFIRMED" status errors

**Deployment Order:**
1. Backup database
2. Pull code
3. Run migrations
4. Build frontend
5. Cache configs
6. Test

