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
            $table->enum('gender', ['male', 'female'])->nullable()->after('shift');
            $table->integer('old_id')->nullable()->after('id')->comment('Original ID from old database');
            $table->boolean('needs_password_upgrade')->default(false)->after('password')->comment('MD5 password needs upgrade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'old_id', 'needs_password_upgrade']);
        });
    }
};
