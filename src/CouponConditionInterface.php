<?php

namespace IICN\Subscription;


use IICN\Subscription\Models\SubscriptionCoupon;

interface CouponConditionInterface
{
    public function handle(SubscriptionCoupon $subscriptionCoupon): bool;

    public function failedMessage(): string;
}
