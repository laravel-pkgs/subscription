<?php
namespace IICN\Subscription\Services\Response;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \IICN\Subscription\Services\Response\Responser success(?string $message = null, array $data = [], int $status = 200, array $headers = [])
 * @method static \IICN\Subscription\Services\Response\Responser error(?string $message = null, ?string $exception = null, int $status = 422, array $headers = [])
 * @method static \IICN\Subscription\Services\Response\Responser data(array $data, ?string $message = null, int $status = 200, array $headers = [])
 *
 * @see \IICN\Subscription\Services\Response\Responser
 */
class SubscriptionResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'subscriptionResponse';
    }
}
