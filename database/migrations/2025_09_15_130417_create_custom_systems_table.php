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
        Schema::create('custom_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('system_components'); // Contains selected components with quantities
            $table->json('calculation_data'); // Contains calculation results
            $table->decimal('estimated_cost', 15, 2);
            $table->decimal('estimated_savings', 15, 2)->nullable();
            $table->decimal('payback_period', 8, 2)->nullable(); // in years
            $table->enum('status', ['DRAFT', 'FINALIZED'])->default('DRAFT');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_systems');
    }
};
