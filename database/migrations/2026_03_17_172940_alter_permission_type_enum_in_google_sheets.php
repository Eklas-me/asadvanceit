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
        // For MySQL, modifying an ENUM is tricky via Schema Builder if it already exists,
        // so we use a DB statement to change the column definition.
        DB::statement("ALTER TABLE google_sheets MODIFY COLUMN permission_type ENUM('public', 'shift_based', 'admin_only', 'specific_users') DEFAULT 'public'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE google_sheets MODIFY COLUMN permission_type ENUM('public', 'shift_based', 'admin_only') DEFAULT 'public'");
    }
};
