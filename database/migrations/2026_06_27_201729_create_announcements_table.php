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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('type', ['notice', 'meeting', 'update'])->default('notice');
            $table->foreignId('published_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('target_level', ['national', 'state', 'lga', 'ward'])->default('national');
            $table->foreignId('state_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('lga_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
