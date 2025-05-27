<?php

namespace IICN\Subscription\Http\Controllers\SubscriptionAdmin;

use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Models\SubscriptionUser;
use Illuminate\Support\Facades\DB;
use Modules\Common\Http\Responses\GenericResponse;
use IICN\Subscription\Http\Resources\SubscriptionUserResources;
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
            ->orderBy('created_at', 'desc')
            ->with(['user', 'subscription'])
            ->paginate();

        return GenericResponse::success(data: SubscriptionUserResources::collection($subscriptionUser));
    }

    public function userSubscriptionGroupedByMonth()
    {
        $now = now();
        $successStatus = 'success';

        $data = [
            'today' => SubscriptionTransaction::query()
                ->with(['subscription'])
                ->select('subscription_id', DB::raw('COUNT(*) as count'), 'agent_type')
                ->where('status', $successStatus)
                ->whereDate('created_at', $now->toDateString())
                ->groupBy('subscription_id', 'agent_type')
                ->get(),

            'last_7_days' => SubscriptionTransaction::query()
                ->with('subscription')
                ->select('subscription_id', DB::raw('COUNT(*) as count'), 'agent_type')
                ->where('status', $successStatus)
                ->whereBetween('created_at', [$now->copy()->subDays(6)->startOfDay(), $now])
                ->groupBy('subscription_id', 'agent_type')
                ->get(),

            'last_month' => SubscriptionTransaction::query()
                ->with('subscription')
                ->select('subscription_id', DB::raw('COUNT(*) as count'), 'agent_type')
                ->where('status', $successStatus)
                ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
                ->groupBy('subscription_id', 'agent_type')
                ->get(),

            'last_year' => SubscriptionTransaction::query()
                ->with('subscription')
                ->select('subscription_id', DB::raw('COUNT(*) as count'), 'agent_type')
                ->where('status', $successStatus)
                ->whereYear('created_at', $now->subYear()->year)
                ->groupBy('subscription_id', 'agent_type')
                ->get(),

            'total' => SubscriptionTransaction::query()
                ->with('subscription')
                ->select('subscription_id', DB::raw('COUNT(*) as count'), 'agent_type')
                ->where('status', $successStatus)
                ->groupBy('subscription_id', 'agent_type')
                ->get(),
        ];

        return GenericResponse::success(data: $data);
    }
}
