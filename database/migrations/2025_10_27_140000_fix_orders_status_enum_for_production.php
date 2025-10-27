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
        // Define valid enum values
        $validStatuses = ['PENDING', 'PROCESSING', 'SCHEDULED', 'INSTALLED', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED', 'REFUNDED'];
        
        // First, update any invalid status values to 'PENDING'
        // This handles any 'CONFIRMED' or other invalid values
        DB::table('orders')
            ->whereNotIn('status', $validStatuses)
            ->update(['status' => 'PENDING']);
        
        // Only modify column for MySQL
        if (DB::getDriverName() !== 'sqlite') {
            // Ensure the enum contains all valid values
            $enumString = "'" . implode("','", $validStatuses) . "'";
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM($enumString) DEFAULT 'PENDING'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
        // This is a one-time fix to ensure data integrity
    }
};

