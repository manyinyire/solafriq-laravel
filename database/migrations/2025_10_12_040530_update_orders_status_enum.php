<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update any invalid status values to valid ones
        DB::table('orders')->whereNotIn('status', [
            'PENDING', 'PROCESSING', 'SCHEDULED', 'INSTALLED', 
            'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED', 'REFUNDED'
        ])->update(['status' => 'PENDING']);
        
        // Only modify column for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            // First, change the column to VARCHAR to avoid enum constraint issues
            DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'PENDING'");
            
            // Then, change it back to ENUM with all the values we need
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'PROCESSING', 'SCHEDULED', 'INSTALLED', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED', 'REFUNDED') DEFAULT 'PENDING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only modify column for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            // Revert to original enum values
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'REFUNDED') DEFAULT 'PENDING'");
        }
    }
};
