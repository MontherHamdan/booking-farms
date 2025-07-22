<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepositeStatusStepToFarmsTable extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->decimal('deposit_rate', 8, 2)->nullable()->after('passengers_count');
            $table->enum('status', ['pending', 'active', 'rejected', 'disabled'])->default('pending')->after('deposit_rate');
            $table->tinyInteger('current_step')->default(1)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn(['deposit_rate', 'status', 'current_step']);
        });
    }
}

