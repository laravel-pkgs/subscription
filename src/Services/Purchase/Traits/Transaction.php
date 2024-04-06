<?php

namespace IICN\Subscription\Services\Purchase\Traits;

use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Subscription;

trait Transaction
{
    public function verifyTransaction(SubscriptionTransaction $transaction, array $response): SubscriptionTransaction
    {
        $transaction->status = Status::SUCCESS;
        $transaction->response_data = $response;

        try {
            $subscriptionUserId = Subscription::create($transaction->subscription_id);
            if ($subscriptionUserId) {
                $transaction->subscription_user_id = $subscriptionUserId;
            }
        } catch (\Exception) {

        }

        $transaction->save();

        return $transaction;
    }

    public function failedTransaction(SubscriptionTransaction $transaction, array $response, string $status): SubscriptionTransaction
    {
        $transaction->status = $status;
        $transaction->response_data = $response;
        $transaction->save();
        return $transaction;
    }
}
