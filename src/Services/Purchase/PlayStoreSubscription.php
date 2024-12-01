<?php

namespace IICN\Subscription\Services\Purchase;

use Carbon\Carbon;
use Google\Client;
use Google\Service\AndroidPublisher;
use IICN\Subscription\Constants\AgentType;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Traits\Transaction;

class PlayStoreSubscription implements HasVerifyPurchase
{
    use Transaction;

    public $service;
    public string $packageName;

    public function __construct()
    {
        $this->packageName = config('subscription.google.package_name');

        $client = new Client();
        $client->setApplicationName($this->packageName);

        $authConfig = [
            "type" => "service_account",
            "project_id" => config('subscription.google.auth_config.project_id'),
            "private_key_id" => config('subscription.google.auth_config.private_key_id'),
            "private_key" => config('subscription.google.auth_config.private_key'),
            "client_email" => config('subscription.google.auth_config.client_email'),
            "client_id" => config('subscription.google.auth_config.client_id'),
            "auth_uri" => "https://accounts.google.com/o/oauth2/auth",
            "token_uri" => "https://oauth2.googleapis.com/token",
            "auth_provider_x509_cert_url" => "https://www.googleapis.com/oauth2/v1/certs",
            "client_x509_cert_url" => config('subscription.google.auth_config.client_x509_cert_url'),
        ];

        $client->setAuthConfig($authConfig);
//        $client->setAuthConfig('path/to/credentials.json');

        $client->addScope('https://www.googleapis.com/auth/androidpublisher');
//        $client->setDeveloperKey(config('subscription.google.app_key'));

        $this->service = new AndroidPublisher($client);
    }

    public function verifyPurchase(SubscriptionTransaction $transaction): array
    {
        $result = $this->service->purchases_subscriptions->get($this->packageName, $transaction->product_id, $transaction->purchase_token);

        $status = $this->getStatus($result->getPaymentState() ?? -1);

        $startAt = Carbon::createFromTimestampMs($result->getStartTimeMillis());
        $expiryDate = Carbon::createFromTimestampMs($result->getExpiryTimeMillis());

        $duration = $expiryDate->diffInDays($startAt);

        // development section
        if ($duration == 0 && $expiryDate->diffInMinutes($startAt) == 5) {
            $duration = 30;
        } elseif ($duration == 0 && $expiryDate->diffInMinutes($startAt) == 30) {
            $duration = 365;
        }

        $productNameSections = explode('_', $transaction->product_id);
        $subscription = \IICN\Subscription\Models\Subscription::query()
            ->orderByRaw('ABS(duration_day - ?) asc', [$duration])
            ->where('type', $productNameSections[0])
            ->firstOrFail();
        $transaction->subscription_id = $subscription->id;
        $transaction->save();

        if ($status == Status::SUCCESS) {
            $transaction = $this->verifyTransaction($transaction, (array)$result);

            return ['status' => true, 'transaction' => $transaction];
        } else {
            $transaction = $this->failedTransaction($transaction, (array)$result, $status);

            return ['status' => false, 'transaction' => $transaction];
        }
    }

    public function getStatus($status): string
    {
        return match ($status) {
            0 => Status::PENDING,
            1 => Status::SUCCESS,
            2 => Status::FREE_TRIAL,
            3 => Status::DEFERRED_PAYMENT,
            default => Status::FAILED,
        };
    }


    public function getAgentType(): string
    {
        return AgentType::GOOGLE_PLAY;
    }

}
