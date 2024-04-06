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
        Schema::create('subscription_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->unsignedBigInteger('subscription_user_id')->nullable()->index();
            $table->unsignedDecimal('price', 10)->nullable();
            $table->string('price_unit')->nullable();
            $table->string('order_id')->unique();
            $table->string('agent_type');
            $table->string('product_id');
            $table->text('purchase_token');
            $table->json('additional_data')->nullable()->comment('app version');
            $table->json('response_data')->nullable();
            $table->unsignedBigInteger('subscription_coupon_id')->nullable()->index();
            $table->enum('status', [
                \IICN\Subscription\Constants\Status::INIT,
                \IICN\Subscription\Constants\Status::PENDING,
                \IICN\Subscription\Constants\Status::SUCCESS,
                \IICN\Subscription\Constants\Status::FAILED,
                ])->default(\IICN\Subscription\Constants\Status::INIT);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_transactions');
    }
};
