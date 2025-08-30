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
        Schema::create('farm_owner_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('account_type', ['iban', 'cliq']);
            
            // IBAN details
            $table->string('iban', 34)->nullable();
            $table->string('bank_name', 100)->nullable();
            
            // CLIQ details
            $table->string('cliq_alias', 50)->nullable();
            $table->string('cliq_phone', 20)->nullable();
            
            $table->string('account_holder_name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->unique(['user_id']); // One account per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_owner_bank_accounts');
    }
};