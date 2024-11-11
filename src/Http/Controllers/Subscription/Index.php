<?php

namespace IICN\Subscription\Http\Controllers\Subscription;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Http\Resources\SubscriptionResources;
use IICN\Subscription\Models\Subscription;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class Index extends Controller
{
    /**
     * @OA\Get(
     *     path="/subscription/api/v1/subscriptions",
     *     operationId="subscriptionsList",
     *     tags={"Subscription"},
     *     summary="subscriptions List",
     *     description="subscriptions List.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Response(
     *         response=200,
     *         description=""
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function __invoke(): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()->withLanguage(app()->getLocale())->get();

        return SubscriptionResources::collection($subscriptions);
    }

    public function indexSubscriptions(): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()->where('sku_code', 'like', '%subscription%')->withLanguage(app()->getLocale())->get();

        return SubscriptionResources::collection($subscriptions);
    }
}
