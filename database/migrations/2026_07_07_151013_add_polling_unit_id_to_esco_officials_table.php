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
        Schema::table('esco_officials', function (Blueprint $table) {
            $table->foreignId('polling_unit_id')->nullable()->after('ward_id')->constrained('polling_units')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('esco_officials', function (Blueprint $table) {
            $table->dropForeign(['polling_unit_id']);
            $table->dropColumn('polling_unit_id');
        });
    }
};
