# Project: SolaFriq Laravel - Solar Energy Management System

## Project Overview

This project is a comprehensive Solar Energy Management System built with Laravel and Vue.js. It is a migration from a Next.js implementation. The application manages solar energy systems, orders, installment plans, warranties, and customer relationships. It is specifically tailored for the Nigerian market, with features for local electricity rates, weather data, and currencies.

**Key Technologies:**

*   **Backend:** Laravel 12, PHP 8.2, MySQL, Redis (optional)
*   **Frontend:** Vue.js 3, Inertia.js, Tailwind CSS, Vite
*   **Authentication:** Laravel Sanctum
*   **Testing:** PHPUnit, Pest

## Building and Running

### Prerequisites

*   PHP 8.2 or higher
*   Composer
*   Node.js 18+ and npm/yarn
*   MySQL 8.0+
*   Redis (optional)

### Installation and Setup

1.  **Clone the repository:**
    ```bash
    git clone <repository-url>
    cd solafriq-laravel
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Set up the environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure your `.env` file** with your database credentials and other settings.

6.  **Run database migrations and seeders:**
    ```bash
    php artisan migrate
    php artisan db:seed
    ```

### Development

*   **Start the development server:**
    ```bash
    npm run dev
    ```
*   **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```

### Building for Production

*   **Build frontend assets:**
    ```bash
    npm run build
    ```

### Testing

*   **Run all tests:**
    ```bash
    php artisan test
    ```
*   **Run feature tests:**
    ```bash
    php artisan test --testsuite=Feature
    ```
*   **Run unit tests:**
    ```bash
    php artisan test --testsuite=Unit
    ```

## Development Conventions

*   The project follows the standard Laravel directory structure.
*   Frontend components are located in `resources/js/Components`.
*   Vue pages are located in `resources/js/Pages`.
*   API routes are defined in `routes/api.php`.
*   Web routes are defined in `routes/web.php`.
*   The project uses Tailwind CSS for styling, with the configuration file at `tailwind.config.js`.
*   The project uses Vite for asset bundling, with the configuration file at `vite.config.js`.
