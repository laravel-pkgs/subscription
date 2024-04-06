<?php

namespace IICN\Subscription\Http\Controllers\Subscription;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Http\Resources\SubscriptionResources;
use IICN\Subscription\Models\Subscription;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexByType extends Controller
{
    public function __invoke(string $type): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()->withLanguage(app()->getLocale())->with('subscriptionAbilities')->where('type', $type)->get();

        return SubscriptionResources::collection($subscriptions);
    }
}
