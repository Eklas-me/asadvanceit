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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('task_date');
            $table->string('account_email')->nullable();
            $table->string('password')->nullable();
            $table->string('tinder_username')->nullable();
            $table->string('token')->nullable();
            $table->string('numbers')->nullable();
            $table->string('lat_long')->nullable();
            $table->text('comments')->nullable();
            $table->string('recovery')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
