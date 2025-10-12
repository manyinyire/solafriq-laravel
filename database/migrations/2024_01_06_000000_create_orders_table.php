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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['PENDING', 'PROCESSING', 'SCHEDULED', 'INSTALLED', 'SHIPPED', 'DELIVERED', 'CANCELLED', 'RETURNED', 'REFUNDED'])->default('PENDING');
            $table->enum('payment_status', ['PENDING', 'PAID', 'FAILED', 'OVERDUE'])->default('PENDING');
            $table->string('payment_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('notes')->nullable();
            
            // Relationships
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Denormalized customer data for guest checkouts
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};