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
        Schema::create('solar_system_features', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name');
            $table->string('feature_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('solar_system_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solar_system_features');
    }
};