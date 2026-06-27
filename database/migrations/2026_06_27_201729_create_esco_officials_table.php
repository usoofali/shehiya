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
        Schema::create('esco_officials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('position');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('state_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('lga_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('appointed_at');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esco_officials');
    }
};
