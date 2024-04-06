<?php

namespace IICN\Subscription;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface HasSubscription
{
    public function subscriptions(): BelongsToMany;

    public function activeSubscriptions(): BelongsToMany;

    public function activeSubscriptionsWithType(string $type): BelongsToMany;
}
