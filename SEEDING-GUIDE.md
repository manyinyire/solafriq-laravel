# Database Seeding Guide for SolaFriq

## Overview

Your SolaFriq project has **multiple seeders** for different deployment scenarios:

| Seeder | Purpose | Use Case |
|--------|---------|----------|
| `DatabaseSeeder` | Full development data | Local development with Faker |
| `ProductionSeeder` | Essential production data | **Shared hosting deployment** ✅ |
| `CompanySettingsSeeder` | Company settings only | Optional configuration |
| `UserSeeder` | Users only | Standalone user creation |
| `SolarSystemSeeder` | Solar systems only | Standalone product data |
| `ProductSeeder` | Products only | Legacy/alternative products |

---

## Comparison: Eventmie-Pro vs SolaFriq

### Eventmie-Pro Seeding
```php
// DatabaseSeeder calls multiple seeders
$this->call(BannersTableSeeder::class);
$this->call(CategoriesTableSeeder::class);
$this->call(CountriesTableSeeder::class);
// ... 15+ seeders total
```

**Characteristics:**
- ✅ Modular (separate seeder per table)
- ✅ Production-ready data
- ✅ No Faker dependency
- ✅ Works on shared hosting
- ⚠️ Large data sets (countries, currencies, etc.)

### SolaFriq Seeding
```php
// DatabaseSeeder - Development (uses Faker)
User::factory(10)->client()->create();
SolarSystem::factory(5)->create();

// ProductionSeeder - Production (no Faker)
User::updateOrCreate(['email' => 'admin@solafriq.com'], [...]);
SolarSystem::updateOrCreate(['name' => 'SolaFriq Home Starter 1kW'], [...]);
```

**Characteristics:**
- ✅ Two modes: Development & Production
- ✅ `ProductionSeeder` is shared hosting ready
- ✅ Uses `updateOrCreate()` (safe to re-run)
- ⚠️ `DatabaseSeeder` requires Faker (dev only)

---

## Deployment Seeding Strategy

### ❌ Don't Use on Shared Hosting
```bash
# This uses Faker and creates random data
php artisan db:seed
# or
php artisan db:seed --class=DatabaseSeeder
```

**Why not?**
- Requires `fakerphp/faker` (dev dependency)
- Creates random/test data
- May not work if `--no-dev` was used with composer

### ✅ Use on Shared Hosting
```bash
# Use the production seeder
php artisan db:seed --class=ProductionSeeder
```

**Why this works:**
- No Faker dependency
- Creates essential data only
- Uses `updateOrCreate()` (idempotent)
- Safe to run multiple times

---

## Step-by-Step Deployment Seeding

### Method 1: Fresh Installation (Recommended)

```bash
# 1. Run migrations
php artisan migrate --force

# 2. Seed production data
php artisan db:seed --class=ProductionSeeder --force

# 3. Verify
php artisan tinker
>>> User::where('email', 'admin@solafriq.com')->first()
>>> SolarSystem::count()
```

### Method 2: Fresh Start (Clean Database)

```bash
# WARNING: This deletes all data!
php artisan migrate:fresh --force --seed --seeder=ProductionSeeder
```

### Method 3: Refresh with Production Data

```bash
# Drop all tables, re-migrate, and seed
php artisan migrate:fresh --force
php artisan db:seed --class=ProductionSeeder --force
```

---

## What ProductionSeeder Creates

### 👥 Users (2)
1. **Admin User**
   - Email: `admin@solafriq.com`
   - Password: `admin123`
   - Role: ADMIN

2. **Test Client**
   - Email: `client@solafriq.com`
   - Password: `client123`
   - Role: CLIENT

### ⚡ Solar Systems (3)

1. **SolaFriq Home Starter 1kW** - $450,000
   - 2 x 500W Panels
   - 100Ah Battery
   - 1000W Inverter
   - Features, Products, Specifications

2. **SolaFriq Home Essential 3kW** - $980,000
   - 6 x 500W Panels
   - 2 x 200Ah Batteries
   - 3000W Inverter
   - Features, Products, Specifications

3. **SolaFriq Commercial Pro 10kW** - $2,850,000
   - 20 x 500W Panels
   - 8 x 200Ah Battery Bank
   - 10kW Hybrid Inverter
   - Features, Products, Specifications

---

## Creating a Custom Production Seeder

If you need to customize the production data:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Call individual seeders
        $this->call([
            ProductionSeeder::class,
            CompanySettingsSeeder::class,
        ]);
        
        // Or add custom data
        // ...
    }
}
```

Then run:
```bash
php artisan db:seed --class=CustomProductionSeeder --force
```

---

## Seeding Best Practices

### ✅ Do's

1. **Use `updateOrCreate()` for production seeders**
   ```php
   User::updateOrCreate(
       ['email' => 'admin@solafriq.com'], // Unique identifier
       ['name' => 'Admin', 'password' => Hash::make('admin123')] // Data
   );
   ```

2. **Avoid Faker in production seeders**
   ```php
   // ❌ Bad (requires Faker)
   'name' => fake()->name()
   
   // ✅ Good (static data)
   'name' => 'Admin User'
   ```

3. **Use `--force` flag on production**
   ```bash
   php artisan db:seed --class=ProductionSeeder --force
   ```

4. **Test locally first**
   ```bash
   php artisan migrate:fresh --seed --seeder=ProductionSeeder
   ```

### ❌ Don'ts

1. **Don't use `User::factory()` in production seeders**
2. **Don't seed sensitive data** (use .env instead)
3. **Don't create too much data** (slow on shared hosting)
4. **Don't forget to hash passwords**

---

## Eventmie-Pro Style Seeding

If you want to structure like Eventmie-Pro (modular seeders):

### Create Individual Seeders

```bash
php artisan make:seeder UsersTableSeeder
php artisan make:seeder SolarSystemsTableSeeder
php artisan make:seeder CompanySettingsTableSeeder
```

### Main Seeder

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            SolarSystemsTableSeeder::class,
            CompanySettingsTableSeeder::class,
        ]);
    }
}
```

**Advantages:**
- ✅ Modular and organized
- ✅ Can run individual seeders
- ✅ Easier to maintain

**Your Current Approach:**
- ✅ Simpler (one file)
- ✅ Faster to run
- ✅ Easier for small projects

---

## Troubleshooting

### Issue 1: "Class 'Faker' not found"

**Cause:** Using `DatabaseSeeder` on production (requires Faker)

**Solution:**
```bash
# Use ProductionSeeder instead
php artisan db:seed --class=ProductionSeeder --force
```

### Issue 2: "SQLSTATE[23000]: Integrity constraint violation"

**Cause:** Duplicate entries (email, name, etc.)

**Solution:**
```php
// Use updateOrCreate instead of create
User::updateOrCreate(
    ['email' => 'admin@solafriq.com'],
    ['name' => 'Admin', ...]
);
```

### Issue 3: Seeder runs but no data appears

**Cause:** Wrong database connection or seeder not found

**Solution:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getDatabaseName()

# Verify seeder exists
php artisan db:seed --class=ProductionSeeder --force
```

### Issue 4: "Class ProductionSeeder does not exist"

**Cause:** Autoload cache issue

**Solution:**
```bash
composer dump-autoload
php artisan db:seed --class=ProductionSeeder --force
```

---

## Automated Deployment Script

Create a deployment script that handles seeding:

```bash
#!/bin/bash
# deploy-with-seed.sh

echo "Running migrations..."
php artisan migrate --force

echo "Seeding production data..."
php artisan db:seed --class=ProductionSeeder --force

echo "Creating storage link..."
php artisan storage:link

echo "Optimizing..."
php artisan optimize

echo "Deployment complete!"
echo "Admin: admin@solafriq.com / admin123"
echo "Client: client@solafriq.com / client123"
```

---

## Security Considerations

### 🔒 Change Default Passwords

After deployment, **immediately change** default passwords:

```bash
php artisan tinker
>>> $admin = User::where('email', 'admin@solafriq.com')->first();
>>> $admin->password = Hash::make('your-secure-password');
>>> $admin->save();
```

Or create a seeder that reads from `.env`:

```php
User::updateOrCreate(
    ['email' => 'admin@solafriq.com'],
    [
        'name' => 'Admin User',
        'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
        // ...
    ]
);
```

Then in `.env`:
```env
ADMIN_PASSWORD=your-secure-password
```

---

## Quick Reference

### Development (Local)
```bash
# Full seeding with Faker
php artisan migrate:fresh --seed
```

### Production (Shared Hosting)
```bash
# Essential data only
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force
```

### Verify Seeding
```bash
php artisan tinker
>>> User::count()
>>> SolarSystem::count()
>>> User::where('role', 'ADMIN')->first()
```

### Re-seed (Safe)
```bash
# ProductionSeeder uses updateOrCreate, safe to re-run
php artisan db:seed --class=ProductionSeeder --force
```

---

## Summary

| Aspect | Eventmie-Pro | SolaFriq |
|--------|--------------|----------|
| Seeder Structure | Modular (15+ files) | Consolidated (1-2 files) |
| Production Ready | ✅ Yes | ✅ Yes (ProductionSeeder) |
| Faker Dependency | ❌ No | ⚠️ Only in DatabaseSeeder |
| Idempotent | ✅ Yes | ✅ Yes (ProductionSeeder) |
| Shared Hosting | ✅ Works | ✅ Works (ProductionSeeder) |

**Your SolaFriq project is ready for production seeding!** 🎉

Use `ProductionSeeder` for deployment, and you'll have the same reliable seeding as Eventmie-Pro.
