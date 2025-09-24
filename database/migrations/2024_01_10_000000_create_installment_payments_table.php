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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};