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
        // Use raw SQL to modify enum - avoids Doctrine DBAL issues
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM(
            'pending_earning',
            'earning_confirmed', 
            'commission',
            'manual_payment',
            'refund',
            'adjustment',
            'bonus'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM(
            'earning',
            'commission', 
            'withdrawal',
            'refund',
            'adjustment',
            'bonus'
        ) NOT NULL");
    }
};