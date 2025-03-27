<?php

namespace IICN\Subscription\Http\Controllers\SubscriptionAdmin;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Models\SubscriptionUser;
use Modules\Common\Http\Responses\GenericResponse;
use IICN\Subscription\Http\Resources\SubscriptionUserResource;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubscriptionController extends Controller
{
    public function userSubscriptions()
    {
        $subscriptionUser = QueryBuilder::for(SubscriptionUser::class)
            ->allowedFilters([
                AllowedFilter::exact('user_id'),
                AllowedFilter::exact('subscription_id'),
            ])
            ->with(['user', 'subscription'])
            ->paginate();

        return GenericResponse::success(SubscriptionUserResource::collection($subscriptionUser));
    }
}
