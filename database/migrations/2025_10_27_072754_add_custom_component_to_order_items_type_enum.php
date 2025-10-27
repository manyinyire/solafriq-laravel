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
        // Modify the enum column to add 'custom_component'
        DB::statement("ALTER TABLE order_items MODIFY COLUMN type ENUM('solar_system', 'product', 'custom_package', 'custom_system', 'custom_component') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE order_items MODIFY COLUMN type ENUM('solar_system', 'product', 'custom_package', 'custom_system') NOT NULL");
    }
};
