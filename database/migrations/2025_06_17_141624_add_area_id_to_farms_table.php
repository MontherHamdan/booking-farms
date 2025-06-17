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
        Schema::table('farms', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('city_id')->constrained('areas')->onDelete('set null');
            
            // Index for better performance
            $table->index('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropIndex(['area_id']);
            $table->dropColumn('area_id');
        });
    }
};