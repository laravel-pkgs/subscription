<?php

namespace IICN\Subscription\Services\Purchase;

use IICN\Subscription\Models\SubscriptionTransaction;

interface HasVerifyPurchase
{
    public function verifyPurchase(SubscriptionTransaction $transaction): array;

    public function getAgentType(): string;
}
