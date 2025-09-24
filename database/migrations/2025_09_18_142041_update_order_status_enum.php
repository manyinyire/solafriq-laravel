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
        // First, temporarily change enum to include both old and new values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'REFUNDED', 'ACCEPTED', 'SCHEDULED', 'INSTALLED', 'RETURNED') NOT NULL DEFAULT 'PENDING'");

        // Update existing order statuses to match new enum values
        DB::table('orders')->where('status', 'PROCESSING')->update(['status' => 'ACCEPTED']);
        DB::table('orders')->where('status', 'SHIPPED')->update(['status' => 'SCHEDULED']);
        DB::table('orders')->where('status', 'DELIVERED')->update(['status' => 'INSTALLED']);
        DB::table('orders')->where('status', 'CANCELLED')->update(['status' => 'RETURNED']);
        DB::table('orders')->where('status', 'REFUNDED')->update(['status' => 'RETURNED']);

        // Finally, set the enum to only the new values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'ACCEPTED', 'SCHEDULED', 'INSTALLED', 'RETURNED') NOT NULL DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back to old values
        DB::table('orders')->where('status', 'ACCEPTED')->update(['status' => 'PROCESSING']);
        DB::table('orders')->where('status', 'SCHEDULED')->update(['status' => 'SHIPPED']);
        DB::table('orders')->where('status', 'INSTALLED')->update(['status' => 'DELIVERED']);
        DB::table('orders')->where('status', 'RETURNED')->update(['status' => 'CANCELLED']);

        // Revert the enum column
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'REFUNDED') NOT NULL DEFAULT 'PENDING'");
    }
};
