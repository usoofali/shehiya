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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('membership_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('dob');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('state_id')->constrained()->restrictOnDelete();
            $table->foreignId('lga_id')->constrained()->restrictOnDelete();
            $table->foreignId('ward_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
