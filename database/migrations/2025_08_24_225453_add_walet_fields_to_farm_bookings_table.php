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
        Schema::table('farm_bookings', function (Blueprint $table) {
            // Financial fields for wallet management
            $table->decimal('platform_commission_rate', 5, 2)->nullable()->after('total_amount');
            $table->decimal('platform_commission_amount', 10, 2)->nullable()->after('platform_commission_rate');
            $table->decimal('farm_owner_earning', 10, 2)->nullable()->after('platform_commission_amount');
            $table->boolean('earnings_processed')->default(false)->after('farm_owner_earning');
            $table->timestamp('earnings_processed_at')->nullable()->after('earnings_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'platform_commission_rate',
                'platform_commission_amount', 
                'farm_owner_earning',
                'earnings_processed',
                'earnings_processed_at'
            ]);
        });
    }
};