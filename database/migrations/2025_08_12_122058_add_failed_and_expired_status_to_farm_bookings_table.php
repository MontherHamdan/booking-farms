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
        // Add 'failed' to booking_status enum and 'expired' to payment_status enum
        if (DB::getDriverName() === 'mysql') {
            // Update booking_status enum to include 'failed'
            DB::statement("ALTER TABLE farm_bookings MODIFY booking_status ENUM('pending', 'confirmed', 'failed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'");
            
            // Update payment_status enum to include 'expired'
            DB::statement("ALTER TABLE farm_bookings MODIFY payment_status ENUM('pending', 'paid', 'failed', 'expired', 'refunded', 'partially_paid') NOT NULL DEFAULT 'pending'");
        } else {
            // For PostgreSQL
            DB::statement("ALTER TYPE booking_status_enum ADD VALUE 'failed'");
            DB::statement("ALTER TYPE payment_status_enum ADD VALUE 'expired'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any failed bookings with expired payments back to cancelled/failed
        DB::table('farm_bookings')
          ->where('booking_status', 'failed')
          ->where('payment_status', 'expired')
          ->update([
              'booking_status' => 'cancelled',
              'payment_status' => 'failed'
          ]);

        // Update other failed bookings to cancelled
        DB::table('farm_bookings')
          ->where('booking_status', 'failed')
          ->update(['booking_status' => 'cancelled']);

        if (DB::getDriverName() === 'mysql') {
            // Revert booking_status enum
            DB::statement("ALTER TABLE farm_bookings MODIFY booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending'");
            
            // Revert payment_status enum
            DB::statement("ALTER TABLE farm_bookings MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'partially_paid') NOT NULL DEFAULT 'pending'");
        } else {
            // PostgreSQL doesn't support removing enum values easily
            // You would need to recreate the enum types
            DB::statement("
                ALTER TABLE farm_bookings 
                ALTER COLUMN booking_status TYPE VARCHAR(20) 
                USING booking_status::text
            ");
            
            DB::statement("
                ALTER TABLE farm_bookings 
                ALTER COLUMN payment_status TYPE VARCHAR(20) 
                USING payment_status::text
            ");
            
            DB::statement("DROP TYPE IF EXISTS booking_status_enum");
            DB::statement("DROP TYPE IF EXISTS payment_status_enum");
            
            DB::statement("
                CREATE TYPE booking_status_enum AS ENUM('pending', 'confirmed', 'cancelled', 'completed')
            ");
            
            DB::statement("
                CREATE TYPE payment_status_enum AS ENUM('pending', 'paid', 'failed', 'refunded', 'partially_paid')
            ");
            
            DB::statement("
                ALTER TABLE farm_bookings 
                ALTER COLUMN booking_status TYPE booking_status_enum 
                USING booking_status::booking_status_enum
            ");
            
            DB::statement("
                ALTER TABLE farm_bookings 
                ALTER COLUMN payment_status TYPE payment_status_enum 
                USING payment_status::payment_status_enum
            ");
        }
    }
};