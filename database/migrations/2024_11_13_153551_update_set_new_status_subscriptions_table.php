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

        \DB::statement("
            ALTER TABLE `subscription_transactions`
                MODIFY `status` ENUM('success', 'failed', 'int', 'pending', 'free_trial', 'deferred_payment')
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci
                NOT NULL
                DEFAULT 'pending';
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
