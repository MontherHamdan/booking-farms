<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farm_owner_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('id_image')->nullable();
            $table->enum('id_verification_status', ['pending', 'verified'])->default('pending');
            $table->timestamp('applied_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'id_verification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farm_owner_applications');
    }
};