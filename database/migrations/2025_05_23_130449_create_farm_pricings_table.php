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
        Schema::create('farm_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->enum('price_type', ['day_use', 'night', 'full_day']);
            $table->decimal('saturday_price', 10, 2);
            $table->decimal('sunday_price', 10, 2);
            $table->decimal('monday_price', 10, 2);
            $table->decimal('tuesday_price', 10, 2);
            $table->decimal('wednesday_price', 10, 2);
            $table->decimal('thursday_price', 10, 2);
            $table->decimal('friday_price', 10, 2);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();
            
            // Ensure one pricing record per farm per price type
            $table->unique(['farm_id', 'price_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_pricings');
    }
};