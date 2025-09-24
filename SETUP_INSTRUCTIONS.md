# SolaFriq Laravel Setup Instructions

Since we encountered network issues with Composer installation, here's how to set up the project manually:

## Database Setup (Manual)

### Step 1: Run the SQL Setup Script
1. Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line)
2. Make sure you're connected to your MySQL server
3. Run the `setup.sql` file in your `sola` database

**Option A: Using phpMyAdmin**
1. Go to phpMyAdmin
2. Select your `sola` database
3. Go to the "SQL" tab
4. Copy and paste the contents of `setup.sql`
5. Click "Go" to execute

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p sola < setup.sql
```

**Option C: Using MySQL Workbench**
1. Open MySQL Workbench
2. Connect to your server
3. Open the `setup.sql` file
4. Execute the script

### Step 2: Verify Database Setup
After running the script, you should have these tables in your `sola` database:
- `users`
- `solar_systems`
- `solar_system_features`
- `solar_system_products`
- `solar_system_specifications`
- `orders`
- `order_items`
- `invoices`
- `installment_plans`
- `installment_payments`
- `warranties`
- `warranty_claims`
- `system_logs`

## Laravel Setup (When Composer Works)

### Step 1: Install Dependencies
```bash
cd solafriq-laravel
composer install
npm install
```

### Step 2: Environment Setup
The `.env` file is already configured for your `sola` database. If needed, update the database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sola
DB_USERNAME=root
DB_PASSWORD=your_password_if_any
```

### Step 3: Generate Application Key
```bash
php artisan key:generate
```

### Step 4: Build Frontend Assets
```bash
npm run build
# or for development
npm run dev
```

### Step 5: Start the Application
```bash
php artisan serve
```

## Test the Setup

### Sample Login Credentials
After running the SQL setup, you can use these test accounts:

**Admin Account:**
- Email: `admin@solafriq.com`
- Password: `password`

**Client Account:**
- Email: `client@solafriq.com`  
- Password: `password`

### API Endpoints to Test

**1. Get Solar Systems (Public)**
```http
GET http://localhost:8000/api/v1/solar-systems
```

**2. User Registration**
```http
POST http://localhost:8000/api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+234-XXX-XXX-XXXX"
}
```

**3. User Login**
```http
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
    "email": "admin@solafriq.com",
    "password": "password"
}
```

**4. Get Solar System Details**
```http
GET http://localhost:8000/api/v1/solar-systems/1
```

## What's Included in the Database

### Sample Solar Systems
1. **SolaFriq Home Starter 1kW** (₦450,000)
   - 2 x 500W Solar Panels
   - 100Ah Lithium Battery
   - 1000W Pure Sine Wave Inverter
   - Complete installation kit

2. **SolaFriq Home Essential 3kW** (₦980,000)
   - 6 x 500W Solar Panels
   - 2 x 200Ah Lithium Batteries
   - 3000W Pure Sine Wave Inverter
   - Smart monitoring system

3. **SolaFriq Commercial Pro 10kW** (₦2,850,000)
   - 20 x 500W Solar Panels
   - 8 x 200Ah Lithium Battery Bank
   - 10kW Three-Phase Hybrid Inverter
   - Grid integration ready

### Features Included
- Complete product specifications
- Component breakdown with pricing
- Performance metrics
- Installation requirements
- Warranty information

## Project Structure Overview

```
solafriq-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/AuthController.php
│   │   └── Api/V1/
│   │       ├── SolarSystemController.php
│   │       └── OrderController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── SolarSystem.php
│   │   ├── Order.php
│   │   └── ...
│   └── Services/
│       ├── OrderProcessingService.php
│       ├── SolarSystemBuilderService.php
│       └── EmailNotificationService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
└── setup.sql (Manual database setup)
```

## Next Steps

1. **Run the SQL setup** to create your database structure
2. **Test the API endpoints** using Postman or similar
3. **Install dependencies** when network issues are resolved
4. **Build frontend components** with Vue.js and Inertia.js
5. **Add payment gateway integration** (Paystack, Flutterwave)
6. **Implement email notifications** for order processing
7. **Add background job processing** for heavy operations

## Support

If you encounter any issues:
1. Check that your MySQL server is running
2. Verify the `sola` database exists
3. Ensure all tables were created successfully
4. Check the Laravel logs in `storage/logs/laravel.log`

The project is now ready for development once the dependency installation is completed!