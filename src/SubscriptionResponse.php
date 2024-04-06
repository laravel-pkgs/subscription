<?php

namespace IICN\Subscription;


interface SubscriptionResponse
{
    public function success(string $message, array $data, int $status, array $headers);

    public function error(string $message, array $exception, int $status, array $headers);

    public function data(array $data, string $message, int $status, array $headers);
}
