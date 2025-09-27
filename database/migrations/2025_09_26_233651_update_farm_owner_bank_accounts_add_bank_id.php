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
        Schema::table('farm_owner_bank_accounts', function (Blueprint $table) {
            // Add bank_id foreign key
            $table->foreignId('bank_id')
                  ->nullable()
                  ->after('account_type')
                  ->constrained('banks')
                  ->onDelete('cascade');
            
            // Remove the old bank_name column (if it exists)
            if (Schema::hasColumn('farm_owner_bank_accounts', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farm_owner_bank_accounts', function (Blueprint $table) {
            // Add back bank_name column
            $table->string('bank_name', 100)->nullable()->after('iban');
            
            // Drop foreign key constraint and bank_id column
            $table->dropForeign(['bank_id']);
            $table->dropColumn('bank_id');
        });
    }
};