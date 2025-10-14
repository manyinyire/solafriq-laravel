# Shared Hosting Setup Guide

## Why Eventmie-Pro Works and SolaFriq Didn't

### Root Cause Analysis

**Eventmie-Pro has:**
1. ✅ Root `.htaccess` (redirects to `public/`)
2. ✅ `public/.htaccess` (Laravel routing)
3. ✅ Complete storage directory structure
4. ✅ All `.gitignore` files in storage directories

**SolaFriq was missing:**
1. ❌ Root `.htaccess` → **NOW FIXED**
2. ❌ `public/.htaccess` → **NOW FIXED**
3. ❌ Storage subdirectories → **NOW FIXED**
4. ❌ Updated `public/index.php` → **NOW FIXED**

---

## Fixed Files Summary

### 1. `.htaccess` (Root Directory)
**Location:** `c:\Users\PRO\Documents\GitHub\solafriq-laravel\.htaccess`

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Purpose:** When you upload the entire Laravel project to `public_html/`, this file redirects all requests to the `public/` subdirectory.

### 2. `public/.htaccess`
**Location:** `c:\Users\PRO\Documents\GitHub\solafriq-laravel\public\.htaccess`

Standard Laravel `.htaccess` with URL rewriting rules.

### 3. `public/index.php`
**Updated to use Laravel 12's `handleRequest()` method** (simpler, like Laravel 11)

### 4. Storage Directory Structure
Created all missing directories:
- `storage/framework/cache/`
- `storage/framework/cache/data/`
- `storage/framework/sessions/`
- `storage/framework/testing/`

---

## Deployment Methods

### Method 1: Upload Entire Project to public_html (Recommended)

**Directory Structure on Server:**
```
public_html/
├── .htaccess              ← Redirects to public/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/                ← Contains index.php
│   ├── .htaccess         ← Laravel routing
│   ├── index.php
│   └── ...
├── resources/
├── routes/
├── storage/               ← Must be writable
├── vendor/
└── .env
```

**How it works:**
1. Browser requests: `https://yourdomain.com/`
2. Root `.htaccess` redirects to: `public/`
3. `public/.htaccess` routes to: `public/index.php`
4. Laravel handles the request

**Steps:**
1. Upload all files to `public_html/`
2. Ensure `.htaccess` files are uploaded (they're hidden!)
3. Set permissions: `chmod -R 755 storage bootstrap/cache`
4. Update `.env` file
5. Run: `php artisan storage:link`
6. Visit your domain

---

### Method 2: Change Document Root (If Supported)

**Directory Structure on Server:**
```
/home/username/
├── solafriq-laravel/      ← Main project folder
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/            ← Set as document root
│   │   ├── .htaccess
│   │   ├── index.php
│   │   └── ...
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   └── .env
└── public_html/ → symlink to solafriq-laravel/public/
```

**Steps:**
1. Upload project to `/home/username/solafriq-laravel/`
2. In cPanel or hosting control panel, change document root to: `/home/username/solafriq-laravel/public`
3. No root `.htaccess` needed (only `public/.htaccess`)
4. More secure (app files outside web root)

---

## Pre-Deployment Checklist

### Local Preparation

```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev

# 2. Build frontend assets
npm install
npm run build

# 3. Clear and cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Files to Upload
- ✅ All project files EXCEPT:
  - ❌ `node_modules/` (too large, rebuild on server if needed)
  - ❌ `.git/` (not needed on production)
  - ❌ `.env` (create new on server)
  - ❌ `storage/logs/*.log` (clear old logs)

### Files to Create on Server
1. `.env` (copy from `.env.production.example`)
2. Update these in `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

---

## Post-Deployment Steps

### 1. Set File Permissions (Linux/Unix)
```bash
# Make storage and cache writable
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# If using Method 1, ensure .htaccess is readable
chmod 644 .htaccess
chmod 644 public/.htaccess
```

### 2. Generate Application Key
```bash
php artisan key:generate
```

### Post-Deployment
- [ ] Run migrations: 
```bash
php artisan migrate --force
```

- [ ] Seed production data: 
```bash
# Use ProductionSeeder (not DatabaseSeeder on production!)
php artisan db:seed --class=ProductionSeeder --force
```

**This creates:**
- Admin user: `admin@solafriq.com` / `admin123`
- Test client: `client@solafriq.com` / `client123`
- 3 solar systems with features, products, and specifications

**⚠️ Important:** Change default passwords immediately after deployment!

- [ ] Create storage symlink: 
```bash
php artisan storage:link
```

- [ ] Clear all caches: 
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

- [ ] Test the application
- [ ] **Change default passwords immediately!** 

---

## Troubleshooting

### Issue 1: 500 Internal Server Error

**Possible Causes:**
1. Missing `.htaccess` files → **FIXED**
2. Wrong file permissions on `storage/`
3. Missing `vendor/` directory
4. PHP version mismatch (need PHP 8.2+)

**Solution:**
```bash
# Check PHP version
php -v

# Reinstall dependencies
composer install --optimize-autoloader --no-dev

# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### Issue 2: Blank Page or "No input file specified"

**Possible Causes:**
1. Missing root `.htaccess` → **FIXED**
2. `mod_rewrite` not enabled
3. Document root pointing to wrong directory

**Solution:**
1. Verify `.htaccess` files exist
2. Contact host to enable `mod_rewrite`
3. Check document root setting

### Issue 3: Routes Not Working (404 on all pages except homepage)

**Possible Causes:**
1. Missing `public/.htaccess` → **FIXED**
2. `mod_rewrite` not enabled
3. `.htaccess` not being read

**Solution:**
1. Verify `public/.htaccess` exists
2. Check if `AllowOverride All` is set in Apache config
3. Test with: `php artisan route:list`

### Issue 4: CSS/JS Not Loading

**Possible Causes:**
1. Assets not built
2. Wrong `APP_URL` in `.env`
3. Missing storage symlink

**Solution:**
```bash
# Rebuild assets
npm run build

# Update .env
APP_URL=https://yourdomain.com

# Create symlink
php artisan storage:link
```

### Issue 5: Session/Cache Errors

**Possible Causes:**
1. Missing storage directories → **FIXED**
2. Wrong permissions

**Solution:**
```bash
# Verify directories exist
ls -la storage/framework/

# Fix permissions
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
```

---

## Comparison: Eventmie-Pro vs SolaFriq

| Feature | Eventmie-Pro | SolaFriq (Before) | SolaFriq (After) |
|---------|--------------|-------------------|------------------|
| Root `.htaccess` | ✅ | ❌ | ✅ |
| `public/.htaccess` | ✅ | ❌ | ✅ |
| Storage structure | ✅ | ❌ | ✅ |
| Laravel version | 11 | 12 | 12 |
| `index.php` style | Simple | Complex | Simple |

---

## Testing Your Deployment

### Local Test
```bash
php artisan serve
# Visit: http://localhost:8000
```

### Server Test
1. Upload files
2. Visit: `https://yourdomain.com`
3. Check for errors in:
   - Browser console (F12)
   - `storage/logs/laravel.log`
   - Server error logs (cPanel → Error Log)

### Debug Mode (Temporarily)
```env
# In .env
APP_DEBUG=true
```
**⚠️ Remember to set back to `false` after debugging!**

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secured
- [ ] `.env` file not publicly accessible
- [ ] `storage/` and `bootstrap/cache/` writable but not executable
- [ ] Remove unnecessary files (`.git/`, `tests/`, etc.)
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set up regular backups

---

## Need More Help?

1. **Check Laravel logs:** `storage/logs/laravel.log`
2. **Check server logs:** Usually in cPanel or hosting control panel
3. **Test locally first:** Ensure it works with `php artisan serve`
4. **Contact your host:** For server-specific issues (mod_rewrite, PHP version, etc.)

---

## Quick Reference: Common Commands

```bash
# Clear everything
php artisan optimize:clear

# Cache everything
php artisan optimize

# View routes
php artisan route:list

# Check environment
php artisan env

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Generate app key
php artisan key:generate
```
