<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('farm_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            
            // Booking Details
            $table->string('booking_reference')->unique();
            $table->enum('price_type', ['day_use', 'night', 'full_day']);
            $table->json('booking_dates'); // Array of dates
            $table->integer('guest_count');
            
            // Pricing Details
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('remaining_amount', 10, 2)->nullable();
            
            // Payment Option
            $table->enum('payment_option', ['full', 'deposit'])
                  ->default('full')
                  ->comment('Payment option selected by user: full payment or deposit only');
            
            // Payment Details
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'failed', 'refunded'])->default('pending');
            $table->enum('booking_status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            
            // Contact Info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            
            // Additional Info
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable(); // For pending bookings
            $table->timestamps();

            // Fixed Indexes - separate indexes instead of composite with JSON
            $table->index(['user_id', 'booking_status'], 'user_booking_status_index');
            $table->index('farm_id', 'farm_bookings_farm_id_index');
            $table->index('booking_reference', 'farm_bookings_reference_index');
            $table->index('stripe_session_id', 'farm_bookings_stripe_session_index');
            $table->index('payment_status', 'farm_bookings_payment_status_index');
            $table->index('booking_status', 'farm_bookings_booking_status_index');
            $table->index('expires_at', 'farm_bookings_expires_at_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('farm_bookings');
    }
};