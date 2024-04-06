<?php

namespace IICN\Subscription\Http\Controllers\Subscription;

use IICN\Subscription\Constants\Status;
use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Http\Requests\VerifyPurchaseRequest;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Appstore;
use IICN\Subscription\Services\Purchase\Playstore;
use IICN\Subscription\Services\Purchase\Purchase;
use IICN\Subscription\Services\Response\SubscriptionResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;

class VerifyPurchase extends Controller
{
    public function __invoke(VerifyPurchaseRequest $request)
    {
        $lock = Cache::lock('transaction_order_id_' . $request->validated('orderId'), 10);

        try {
            $lock->block(5);

            $subscriptionTransaction = SubscriptionTransaction::query()->where('order_id', $request->validated('orderId'))->first();

            if ($subscriptionTransaction) {
                $message = $this->getMessage($subscriptionTransaction);
                return SubscriptionResponse::data(['purchase_status' => $subscriptionTransaction->status], $message);
            }

            if ($request->gateway == 'appStore') {
                $playstore = new Purchase(new Appstore());
            } elseif($request->gateway == 'playStore') {
                $playstore = new Purchase(new Playstore());
            } else {
                return SubscriptionResponse::data(['purchase_status' => Status::FAILED], trans('subscription::messages.payment_not_valid'));
            }

            $result = $playstore->verify($request->skuCode, $request->purchaseToken, $request->orderId, $request->price);

            $message = $this->getMessage($result['transaction']);

            return SubscriptionResponse::data(['purchase_status' => $result['transaction']->status], $message);
        } catch (LockTimeoutException $e) {
            return SubscriptionResponse::data(['purchase_status' => Status::FAILED], trans('subscription::messages.payment_not_valid'));
        } finally {
            $lock?->release();
        }
    }

    private function getMessage($transaction): string
    {
        if($transaction->status == Status::SUCCESS) {
            $message = trans('subscription::messages.payment_done');
        } elseif ($transaction->status == Status::FAILED) {
            $message = trans('subscription::messages.payment_not_valid');
        } elseif ($transaction->status == Status::PENDING) {
            $message = trans('subscription::messages.payment_pending');
        } else {
            $message = trans('subscription::messages.payment_not_valid');
        }
        return $message;
    }
}
