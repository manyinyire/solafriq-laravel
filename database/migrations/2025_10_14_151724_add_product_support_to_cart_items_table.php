<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('solar_system_id')->constrained()->onDelete('cascade');
            $table->string('item_type')->default('solar_system')->after('product_id'); // 'solar_system' or 'product'
            
            // Make solar_system_id nullable since we can have products too
            $table->foreignId('solar_system_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'item_type']);
        });
    }
};
