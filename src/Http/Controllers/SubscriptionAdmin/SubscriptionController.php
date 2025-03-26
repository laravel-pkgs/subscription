<?php

namespace IICN\Subscription\Http\Controllers\SubscriptionAdmin;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Models\SubscriptionUser;
use Modules\Common\Http\Responses\GenericResponse;

class SubscriptionController extends Controller
{
    public function userSubscriptions()
    {
        $subscriptionUser = SubscriptionUser::query()->with('user')->paginate();

        return GenericResponse::success($subscriptionUser);
    }
}
