<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL specific: Alter ENUM column to add 'converted' status
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'sent', 'accepted', 'rejected', 'expired', 'converted') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert ENUM column (remove 'converted')
        DB::statement("ALTER TABLE quotes MODIFY COLUMN status ENUM('pending', 'sent', 'accepted', 'rejected', 'expired') DEFAULT 'pending'");
    }
};
