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
        Schema::table('users', function (Blueprint $table) {
            // Drop the old city column if it exists
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            
            // Add the new city_id column as foreign key
            $table->unsignedBigInteger('city_id')->nullable()->after('email');
            
            // Add foreign key constraint
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key and column
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
            
            // Add back the old city column (assuming it was a string)
            $table->string('city')->nullable();
        });
    }
};