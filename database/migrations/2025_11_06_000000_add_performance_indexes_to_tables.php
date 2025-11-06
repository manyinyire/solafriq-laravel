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
        // Add indexes to orders table for better query performance
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('user_id');
            $table->index('tracking_number');
            $table->index('created_at');
        });

        // Add indexes to products table
        Schema::table('products', function (Blueprint $table) {
            $table->index('category');
            $table->index('stock_quantity');
            $table->index('is_active');
        });

        // Add indexes to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
        });

        // Add indexes to invoices table
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('payment_status');
        });

        // Add indexes to warranties table
        Schema::table('warranties', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['tracking_number']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['stock_quantity']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('warranties', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
        });
    }
};
