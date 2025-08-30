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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('booking_id')->nullable(); // Related booking if applicable
            $table->string('reference')->unique(); // Transaction reference
            $table->enum('type', [
                'earning',          // Money earned from booking
                'commission',       // Platform commission deduction
                'withdrawal',       // Money withdrawn by farm owner
                'refund',          // Refund deducted (if booking cancelled)
                'adjustment',      // Manual admin adjustment
                'bonus'            // Admin bonus
            ]);
            $table->decimal('amount', 10, 2); // Can be positive or negative
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('description');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('metadata')->nullable(); // Store additional data like commission rate, etc.
            $table->unsignedBigInteger('processed_by')->nullable(); // Admin who processed
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('farm_owner_wallets')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('farm_bookings')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['wallet_id', 'type']);
            $table->index('booking_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};