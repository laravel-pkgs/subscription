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

class AppStoreSubscription implements HasVerifyPurchase
{
    use Transaction;

    public string $secret;
    public function __construct()
    {
        $this->secret = config('subscription.apple.secret');
    }

    public function verifyPurchase(SubscriptionTransaction $transaction): array
    {

        $response = $this->request($transaction->purchase_token);

        if (is_null($response)) {
            $transaction = $this->failedTransaction($transaction, [], Status::FAILED);

            return ['status' => false, 'transaction' => $transaction];
        } elseif($response->status() !== 200) {
            $transaction = $this->failedTransaction($transaction, $response->json(), Status::FAILED);

            return ['status' => false, 'transaction' => $transaction];
        }

        $skuCode = Str::replace('_', '-', $transaction->product_id);

        $subscription = \IICN\Subscription\Models\Subscription::query()
            ->where('sku_code', $skuCode)
            ->firstOrFail();

        $status = $this->getStatus($response->json('status'));

        if ($status == Status::SUCCESS) {
            $transaction->subscription_id = $subscription->id;
            $transaction->save();

            $transaction = $this->verifyTransaction($transaction, $response->json());
            return ['status' => true, 'transaction' => $transaction];
        } elseif ($response->json('status') == 21007) {
            $response = $this->request($transaction->purchase_token, "https://sandbox.itunes.apple.com/verifyReceipt/");
            $transaction->subscription_id = $subscription->id;
            $transaction->save();

            $transaction = $this->verifyTransaction($transaction, $response->json());

            return ['status' => true, 'transaction' => $transaction];
        } else {
            $transaction = $this->failedTransaction($transaction, [$response->json()], $status);
            return ['status' => false, 'transaction' => $transaction];
        }
    }

    public function getStatus($status): string
    {
        return match ($status) {
            0 => Status::SUCCESS,
            2 => Status::PENDING,
            default => Status::FAILED,
        };
    }

    public function request(string $receiptData, $url = "https://buy.itunes.apple.com/verifyReceipt")
    {
        $data = [
            'receipt-data' => $receiptData,
            "password" => $this->secret,
            "exclude-old-transactions" => true
        ];

        try {
            return Http::post($url, $data);
        } catch (\Exception) {

        }

        return null;
    }

    public function getAgentType(): string
    {
        return AgentType::APP_STORE;
    }

}
