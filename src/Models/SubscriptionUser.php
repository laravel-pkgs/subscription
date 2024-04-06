<?php

namespace IICN\Subscription\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SubscriptionUser extends Pivot
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 'subscription_id', 'created_at', 'expiry_at', 'remaining_number'
    ];

    protected $casts = [
        'remaining_number' => 'json',
        'expiry_at' => 'datetime:Y-m-d H:i:s'
    ];
}
