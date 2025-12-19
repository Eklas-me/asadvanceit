<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add a virtual generated column for efficient duplicate checking
        // We use MD5 of the normalized token (trimmed, lowercase) to create a short, indexable hash
        // Note: We use raw SQL because Laravel schema builder support for generated columns varies

        DB::statement("
            ALTER TABLE live_tokens 
            ADD COLUMN token_hash CHAR(32) 
            GENERATED ALWAYS AS (MD5(LOWER(TRIM(TRAILING '/' FROM live_token)))) VIRTUAL
        ");

        Schema::table('live_tokens', function (Blueprint $table) {
            $table->index('token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('live_tokens', function (Blueprint $table) {
            $table->dropIndex(['token_hash']);
            $table->dropColumn('token_hash');
        });
    }
};
