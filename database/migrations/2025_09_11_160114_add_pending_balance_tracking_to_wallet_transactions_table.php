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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('pending_balance_before', 10, 2)->nullable()->after('balance_after');
            $table->decimal('pending_balance_after', 10, 2)->nullable()->after('pending_balance_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['pending_balance_before', 'pending_balance_after']);
        });
    }
};
