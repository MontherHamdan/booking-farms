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
            $table->boolean('earnings_confirmed')->default(false)->after('earnings_processed_at');
            $table->timestamp('earnings_confirmed_at')->nullable()->after('earnings_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_bookings', function (Blueprint $table) {
            $table->dropColumn(['earnings_confirmed', 'earnings_confirmed_at']);
        });
    }
};
