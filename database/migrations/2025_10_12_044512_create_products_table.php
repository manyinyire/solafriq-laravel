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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', [
                'SOLAR_PANEL',
                'INVERTER',
                'BATTERY',
                'CHARGE_CONTROLLER',
                'MOUNTING',
                'CABLES',
                'ACCESSORIES'
            ]);
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('specifications')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->string('unit')->default('piece');
            $table->decimal('power_rating', 10, 2)->nullable(); // For panels and inverters (Watts)
            $table->decimal('capacity', 10, 2)->nullable(); // For batteries (Ah)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
