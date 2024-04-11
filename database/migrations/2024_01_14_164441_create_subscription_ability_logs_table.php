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
        Schema::create('subscription_ability_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_ability_id')->index();
            $table->unsignedBigInteger('subscription_user_id')->index()->nullable();
            $table->unsignedBigInteger('user_id')->index()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_ability_logs');
    }
};
