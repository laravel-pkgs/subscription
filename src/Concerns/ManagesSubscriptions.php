<?php

namespace IICN\Subscription\Concerns;

use Carbon\Carbon;
use IICN\Subscription\Models\SubscriptionUser;
use IICN\Subscription\Services\Subscription;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait ManagesSubscriptions
{
    public function newSubscription(int $subscription_id, ?int $durationDay = null, ?array $remainingNumber = null): bool
    {
        $subscription = new Subscription($this);

        return $subscription->create($subscription_id, $durationDay, $remainingNumber);
    }

    public function useSubscription(string $type): bool
    {
        $subscription = new Subscription($this);

        return $subscription->used($type);
    }

    public function getActiveSubscriptionsWithTypeAndHasCount(string $type): array
    {
        $subscription = new Subscription($this);

        return $subscription->getActiveWithCount($type);
    }

    public function getFirstActiveSubscription(): \IICN\Subscription\Models\Subscription|null
    {
        return $this->activeSubscriptions()->first();
    }

    public function getActiveSubscriptionType(): string|null
    {
        return $this->getFirstActiveSubscription()->type ?? null;
    }

    public function getActiveSubscriptionExpiryAt(): string|null
    {
        return $this->getFirstActiveSubscription()->pivot->expiry_at ?? null;
    }

    public function getActiveSubscriptionAbilityTypes(): array
    {
        $types = [];
        foreach ($this->activeSubscriptions()->get() as $subscription) {
            $types = array_merge($types, $subscription->subscriptionAbilities()->pluck('type')->toArray());
        }
        return array_unique($types);
    }

    public function getActiveSubscriptionsCountWithTypeAndHasCount(string $type): int
    {
        $subscription = new Subscription($this);

        $subscriptions = $subscription->getActiveWithCount($type);

        $count = 0;
        foreach ($subscriptions as $subscription) {
            $count += (int) $subscription->pivot->remaining_number[$type] ?? 0;
        }

        return $count;
    }

    public function getSubscriptionTypes(): array
    {
        $subscription = new Subscription($this);

        return $subscription->getSubscriptionTypes();
    }

    public function activeSubscriptionsWithType(string $type): BelongsToMany
    {
        return $this->activeSubscriptions()->whereHas('subscriptionAbilities', function ($query) use ($type) {
            $query->where('type', $type);
        });
    }

    public function activeSubscriptions(): BelongsToMany
    {
        return $this->subscriptions()->where(function($query) {
            $query->where('subscription_user.expiry_at', '>', Carbon::now())
                ->orWhereNull('subscription_user.expiry_at');
        });
    }

    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(\IICN\Subscription\Models\Subscription::class, SubscriptionUser::class, 'user_id', 'subscription_id')
            ->withPivot(['expiry_at', 'remaining_number', 'id'])->withTimestamps()
            ->orderBy('priority', 'desc')
            ->orderBy('pivot_created_at');
    }
}
