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
        Schema::table('live_tokens', function (Blueprint $table) {
            // Index for timestamp checking (whereBetween)
            $table->index('insert_time');

            // Composite index for the dashboard join (user_id + insert_time)
            $table->index(['user_id', 'insert_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_tokens', function (Blueprint $table) {
            $table->dropIndex(['insert_time']);
            $table->dropIndex(['user_id', 'insert_time']);
        });
    }
};
