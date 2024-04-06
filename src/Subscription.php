<?php
namespace IICN\Subscription;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \IICN\Subscription\Services\Subscription create(int $subscription_id, ?int $durationDay = null, ?array $remainingNumber = null)
 * @method static \IICN\Subscription\Services\Subscription used(string $type)
 * @method static \IICN\Subscription\Services\Subscription canUse(string $type)
 * @method static \IICN\Subscription\Services\Subscription getSubscriptionTypes()
 * @method static \IICN\Subscription\Services\Subscription getSubscriptionableId()
 *
 * @see \IICN\Subscription\Services\Subscription
 */
class Subscription extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'subscription';
    }
}
