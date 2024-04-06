<?php

namespace IICN\Subscription\Services\Purchase;

use Google\Client;
use Google\Service\AndroidPublisher;
use IICN\Subscription\Constants\AgentType;
use IICN\Subscription\Constants\Status;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Traits\Transaction;

class Playstore implements HasVerifyPurchase
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
        $result = $this->service->purchases_products->get($this->packageName, $transaction->product_id, $transaction->purchase_token);

        $status = $this->getStatus($result['purchaseState'] ?? -1);

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
            0 => Status::SUCCESS,
            1 => Status::FAILED,
            2 => Status::PENDING,
            default => Status::INIT,
        };
    }


    public function getAgentType(): string
    {
        return AgentType::GOOGLE_PLAY;
    }
}
