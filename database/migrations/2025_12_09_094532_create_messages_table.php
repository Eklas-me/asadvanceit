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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type'); // 'admin' or 'user'
            $table->foreignId('sender_id');
            $table->string('receiver_type'); // 'admin' or 'user'
            $table->foreignId('receiver_id');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
