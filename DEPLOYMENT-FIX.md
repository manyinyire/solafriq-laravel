# Deployment Issues Fixed

## Why Eventmie-Pro Works on Shared Hosting But SolaFriq Didn't

### The Problem
When you upload **eventmie-pro** to `public_html/`, it works immediately. But **solafriq-laravel** uploaded the same way gives errors (500, blank page, or "No input file specified").

### Root Cause Analysis

**Eventmie-Pro has these files:**
1. ✅ Root `.htaccess` that redirects to `public/`
2. ✅ `public/.htaccess` with Laravel routing rules
3. ✅ Complete storage directory structure
4. ✅ Simpler `public/index.php` (Laravel 11 style)

**SolaFriq was missing:**
1. ❌ Root `.htaccess` → **NOW FIXED**
2. ❌ `public/.htaccess` → **NOW FIXED**
3. ❌ Storage subdirectories → **NOW FIXED**
4. ❌ Outdated `public/index.php` → **NOW FIXED**

## Problems Identified

### 1. **Missing .htaccess Files** ❌
Your solafriq-laravel project was missing critical `.htaccess` files that eventmie-pro has:
- **Root `.htaccess`**: Redirects all requests to the `public/` folder
- **`public/.htaccess`**: Handles URL rewriting for Laravel routes

**Impact:** Without these, the server doesn't know how to route requests to Laravel's front controller.

### 2. **Incomplete Storage Directory Structure** ❌
Missing required Laravel storage subdirectories:
- `storage/framework/cache/`
- `storage/framework/cache/data/`
- `storage/framework/sessions/`
- `storage/framework/testing/`

**Impact:** Laravel crashes when trying to write cache or session files.

### 3. **Outdated public/index.php** ❌
- **eventmie-pro**: Uses Laravel 11's simpler `handleRequest()` method (17 lines)
- **solafriq-laravel**: Used Laravel 12's verbose kernel approach (55 lines)

**Impact:** The newer `handleRequest()` method is more compatible with shared hosting environments.

## Files Created/Fixed ✅

### 1. Root `.htaccess`
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```
**Purpose**: Redirects all requests to the `public/` directory

### 2. `public/.htaccess`
Standard Laravel `.htaccess` with:
- URL rewriting to `index.php`
- Authorization header handling
- Trailing slash removal
- Front controller pattern

### 3. Storage Directory Structure
Created all missing directories with proper `.gitignore` files

## Deployment Checklist for Shared Hosting

### Pre-Deployment
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache`
- [ ] Run `npm run build` (for Vite assets)

### File Permissions (Linux/Unix servers)
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Environment Configuration
1. Copy `.env.production.example` to `.env`
2. Update database credentials
3. Set `APP_ENV=production`
4. Set `APP_DEBUG=false`
5. Update `APP_URL` to your domain
6. Generate new `APP_KEY`: `php artisan key:generate`

### Directory Structure on Server
```
public_html/              (or www, htdocs)
├── .htaccess            ← Root htaccess (redirects to public/)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/              ← This should be your document root
│   ├── .htaccess       ← Public htaccess (Laravel routing)
│   ├── index.php
│   └── ...
├── resources/
├── routes/
├── storage/            ← Must be writable (755 or 775)
└── vendor/
```

### Alternative: Document Root Setup
If your hosting allows changing the document root:
1. Point document root to `public/` folder
2. You won't need the root `.htaccess`
3. Only `public/.htaccess` will be used

### Post-Deployment
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed database (if needed): `php artisan db:seed --force`
- [ ] Create storage symlink: `php artisan storage:link`
- [ ] Clear all caches: `php artisan optimize:clear`
- [ ] Test the application

## Common Shared Hosting Issues

### Issue 1: 500 Internal Server Error
**Causes**:
- Missing `.htaccess` files ✅ FIXED
- Wrong file permissions on `storage/` and `bootstrap/cache/`
- Missing `vendor/` directory (run `composer install`)

### Issue 2: Blank Page or "No input file specified"
**Causes**:
- Missing root `.htaccess` ✅ FIXED
- Document root not pointing to `public/`

### Issue 3: Routes Not Working (404 errors)
**Causes**:
- Missing `public/.htaccess` ✅ FIXED
- `mod_rewrite` not enabled on server

### Issue 4: Session/Cache Errors
**Causes**:
- Missing storage directories ✅ FIXED
- Wrong permissions on `storage/framework/`

## Why Eventmie-Pro Works

1. **Complete .htaccess setup**: Both root and public htaccess files present
2. **Proper storage structure**: All required directories exist
3. **Simpler Laravel 11 bootstrap**: Uses newer `handleRequest()` method
4. **Web installer**: Handles setup automatically

## Testing Your Deployment

1. **Local Test** (if possible):
   ```bash
   php artisan serve
   ```
   Visit: http://localhost:8000

2. **Shared Server Test**:
   - Upload all files
   - Ensure `.htaccess` files are uploaded (they're hidden files!)
   - Check file permissions
   - Visit your domain

3. **Debug Mode** (temporarily):
   - Set `APP_DEBUG=true` in `.env`
   - Check error logs in `storage/logs/laravel.log`
   - Set back to `false` when done

## Additional Notes

- **Hidden Files**: Ensure `.htaccess` files are uploaded (use FTP client that shows hidden files)
- **PHP Version**: Your server must support PHP 8.2+ (as per composer.json)
- **Extensions Required**: Check `phpinfo()` for required extensions
- **Symbolic Link**: `public/storage` should link to `storage/app/public`

## Need Help?

If issues persist, check:
1. Server error logs (usually in cPanel or hosting control panel)
2. Laravel logs: `storage/logs/laravel.log`
3. PHP version: `php -v`
4. Apache modules: `mod_rewrite` must be enabled
