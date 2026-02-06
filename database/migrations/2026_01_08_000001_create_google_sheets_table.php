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
        if (!Schema::hasTable('google_sheets')) {
            Schema::create('google_sheets', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->text('url');
                $table->string('icon')->default('fas fa-file-excel');
                $table->enum('permission_type', ['public', 'shift_based', 'admin_only'])->default('public');
                $table->string('shift')->nullable(); // For shift_based permission
                $table->boolean('is_visible')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_sheets');
    }
};
