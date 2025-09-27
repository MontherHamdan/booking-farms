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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 100);
            $table->string('name_ar', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index(['name_en', 'is_active']);
            $table->index(['name_ar', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};