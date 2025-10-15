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
        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'group')) {
                $table->string('group')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'display_name')) {
                $table->string('display_name')->nullable();
            }
            if (!Schema::hasColumn('company_settings', 'order')) {
                $table->integer('order')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'display_name', 'order']);
        });
    }
};
