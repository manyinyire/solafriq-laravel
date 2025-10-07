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
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->after('description'); // 'panel', 'inverter', 'battery', 'mounting', 'accessory'
            $table->string('brand')->nullable()->after('category');
            $table->string('model')->nullable()->after('brand');
            $table->json('specifications')->nullable()->after('image_url');
            $table->integer('stock_quantity')->default(0)->after('specifications');
            $table->string('unit')->default('piece')->after('stock_quantity');
            $table->decimal('power_rating', 10, 2)->nullable()->after('unit');
            $table->decimal('capacity', 10, 2)->nullable()->after('power_rating');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'brand',
                'model',
                'specifications',
                'stock_quantity',
                'unit',
                'power_rating',
                'capacity',
                'sort_order',
            ]);
        });
    }
};
