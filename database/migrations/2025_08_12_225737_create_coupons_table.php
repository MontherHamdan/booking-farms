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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->enum('discount_type', ['percentage', 'fixed_amount']);
            $table->decimal('discount_value', 8, 2);
            $table->decimal('max_discount', 8, 2)->nullable(); // Only for percentage discounts
            $table->integer('usage_limit')->nullable(); // NULL = unlimited
            $table->enum('platform', ['web', 'mobile', 'both'])->default('both');
            $table->json('cities')->nullable(); // NULL = all cities, array of city IDs for specific cities
            $table->enum('usage_limit_per_user_type', ['single', 'multiple', 'unlimited'])->default('single');
            $table->integer('usage_limit_per_user_count')->nullable(); // Only for 'multiple' type
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['code', 'is_active']);
            $table->index(['start_date', 'end_date']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};