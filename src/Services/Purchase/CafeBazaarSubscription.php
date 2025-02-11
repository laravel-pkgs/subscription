<?php

namespace IICN\Subscription\Services\Purchase;

use Carbon\Carbon;
use Google\Client;
use Google\Service\AndroidPublisher;
use IICN\Subscription\Constants\AgentType;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Traits\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CafeBazaarSubscription implements HasVerifyPurchase
{
    use Transaction;

    public string $secret;
    public function __construct()
    {
        $this->secret = config('subscription.apple.secret');
    }

    public function verifyPurchase(SubscriptionTransaction $transaction): array
    {
        $skuCode = Str::replace('_', '-', $transaction->product_id);

        $response = $this->request($skuCode, $transaction->purchase_token);

        if (is_null($response)) {
            $transaction = $this->failedTransaction($transaction, [], Status::FAILED);

            return ['status' => false, 'transaction' => $transaction];
        } elseif($response->status() !== 200) {
            $transaction = $this->failedTransaction($transaction, $response->json(), Status::FAILED);

            return ['status' => false, 'transaction' => $transaction];
        }

        $subscription = \IICN\Subscription\Models\Subscription::query()
            ->where('sku_code', $skuCode)
            ->firstOrFail();

//        $status = $this->getStatus($response->json('status'));
        $transaction = $this->verifyTransaction($transaction, $response->json());
        return ['status' => true, 'transaction' => $transaction];

//        if ($status == Status::SUCCESS || $response->json('status') == 21007) {
//            $response = $this->request($transaction->purchase_token, "https://sandbox.itunes.apple.com/verifyReceipt/");
//            $transaction->subscription_id = $subscription->id;
//            $transaction->save();
//
//            $transaction = $this->verifyTransaction($transaction, $response->json());
//            return ['status' => true, 'transaction' => $transaction];
//        } else {
//            $transaction = $this->failedTransaction($transaction, $response->json(), $status);
//            return ['status' => false, 'transaction' => $transaction];
//        }
    }

    public function getStatus($status): string
    {
        return match ($status) {
            0 => Status::SUCCESS,
            2 => Status::PENDING,
            default => Status::FAILED,
        };
    }

    public function request($subscriptionName, $purchaseToken)
    {
        $packageName = env('ANDROID_APP_PACKAGE_NAME', 'com.nasimeferdows.main');

        try {
            return Http::withHeaders([
                'CAFEBAZAAR-PISHKHAN-API-SECRET' => env('CAFEBAZAAR_API_SECRET'),
                'accept' => 'application/json',
            ])->post("https://pardakht.cafebazaar.ir/devapi/v2/api/applications/$packageName/subscriptions/$subscriptionName/purchases/$purchaseToken/");
        } catch (\Exception) {

        }

        return null;
    }

    public function getAgentType(): string
    {
        return AgentType::APP_STORE;
    }

}
