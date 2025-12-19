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
        Schema::dropIfExists('live_tokens_temp');
        Schema::dropIfExists('temp_old_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tables are temporary and cleanup-related, no need to restore
    }
};
