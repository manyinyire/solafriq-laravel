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
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('down_payment', 12, 2);
            $table->decimal('monthly_payment', 12, 2);
            $table->integer('payment_duration_months');
            $table->enum('status', ['PENDING', 'ACTIVE', 'COMPLETED', 'DEFAULTED'])->default('PENDING');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('solar_system_id')->constrained()->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};