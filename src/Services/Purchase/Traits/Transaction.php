<?php

namespace IICN\Subscription\Services\Purchase\Traits;

use App\Models\User;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Subscription;
use IICN\Subscription\Services\Subscription as SubscriptionService;

trait Transaction
{
    public function verifyTransaction(SubscriptionTransaction $transaction, array $response): SubscriptionTransaction
    {
        $transaction->status = Status::SUCCESS;
        $transaction->response_data = $response;

        try {
            if (app()->runningInConsole()) {

                $subscriptionUserId = (new SubscriptionService(User::query()->find($transaction->user_id)))->create($transaction->subscription_id);

            } else {
                $subscriptionUserId = Subscription::create($transaction->subscription_id);
            }

            if ($subscriptionUserId) {
                $transaction->subscription_user_id = $subscriptionUserId;
            }
        } catch (\Exception $exception) {
            throw $exception;
        }

        $transaction->save();

        return $transaction;
    }

    public function failedTransaction(SubscriptionTransaction $transaction, ?array $response, string $status): SubscriptionTransaction
    {
        $transaction->status = $status;
        $transaction->response_data = $response;
        $transaction->save();
        return $transaction;
    }
}
