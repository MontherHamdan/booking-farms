<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->string('description')->nullable();
            $table->timestamps();
            
            // Index
            $table->index('key');
        });
        
        // Insert default settings
        DB::table('platform_settings')->insert([
            [
                'key' => 'transfer_frequency_days',
                'value' => '14',
                'description' => 'Number of days between manual transfers to farm owners',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'minimum_transfer_amount',
                'value' => '50',
                'description' => 'Minimum amount required for manual transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};