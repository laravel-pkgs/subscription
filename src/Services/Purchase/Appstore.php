<?php

namespace IICN\Subscription\Services\Purchase;

use IICN\Subscription\Constants\AgentType;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use Illuminate\Support\Facades\Http;
use IICN\Subscription\Services\Purchase\Traits\Transaction;

class Appstore implements HasVerifyPurchase
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

        if ($response->json('status') == 21007) {
            $response = $this->request($transaction->purchase_token, "https://sandbox.itunes.apple.com/verifyReceipt");
        }

        $status = $this->getStatus($response->json('status'));

        if ($status == Status::SUCCESS) {
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
