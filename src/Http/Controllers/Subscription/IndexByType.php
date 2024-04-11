<?php

namespace IICN\Subscription\Http\Controllers\Subscription;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Http\Resources\SubscriptionResources;
use IICN\Subscription\Models\Subscription;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexByType extends Controller
{
    /**
     * @OA\Get(
     *     path="/subscription/api/v1/subscriptions/types/{type}",
     *     operationId="subscriptionsListByType",
     *     tags={"Subscription"},
     *     summary="subscriptions List By Type",
     *     description="subscriptions List Filter By Type.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         description="The type of the subscription",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
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
    public function __invoke(string $type): AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()->withLanguage(app()->getLocale())->with('subscriptionAbilities')->where('type', $type)->get();

        return SubscriptionResources::collection($subscriptions);
    }
}
