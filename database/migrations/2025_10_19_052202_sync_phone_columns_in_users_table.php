<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sync phone_number values to phone column for consistency
     */
    public function up(): void
    {
        // Copy phone_number values to phone column where phone is null
        DB::table('users')
            ->whereNotNull('phone_number')
            ->whereNull('phone')
            ->update(['phone' => DB::raw('phone_number')]);

        // If phone has value but phone_number is null, copy phone to phone_number
        DB::table('users')
            ->whereNotNull('phone')
            ->whereNull('phone_number')
            ->update(['phone_number' => DB::raw('phone')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data sync
    }
};
