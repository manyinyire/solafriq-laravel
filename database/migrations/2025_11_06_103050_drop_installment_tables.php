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
        // Drop foreign key constraints first
        Schema::table('installment_payments', function (Blueprint $table) {
            $table->dropForeign(['installment_plan_id']);
        });

        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['solar_system_id']);
        });

        // Drop tables
        Schema::dropIfExists('installment_payments');
        Schema::dropIfExists('installment_plans');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables if needed (for rollback)
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

        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['PENDING', 'PAID', 'FAILED', 'OVERDUE'])->default('PENDING');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('installment_plan_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }
};
