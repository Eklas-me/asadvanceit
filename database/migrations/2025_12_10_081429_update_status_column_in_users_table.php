<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change enum to string to allow 'suspended', 'rejected', etc.
            // Note: DB::statement might be needed for enum modification in some DBs, 
            // but change() often works if dbal is present. 
            // Fallback: simple string type is safest for flexibility.
            $table->string('status', 50)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert strict enum if needed, though string is generally fine
            // $table->enum('status', ['pending', 'active'])->default('pending')->change();
        });
    }
};
