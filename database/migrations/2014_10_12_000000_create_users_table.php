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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();        
            $table->string('phone')->unique()->nullable();          
            $table->string('city');
            $table->string('avatar')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // add columns to hold the OTP code and expiry
            $table->string('otp_code', 255)->nullable();
            $table->string('security_token')->nullable();
            $table->timestamp('otp_expires_at')->nullable();

            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
