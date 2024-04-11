<?php

namespace IICN\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionAbilityLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['subscription_user_id', 'subscription_ability_id', 'user_id'];


    public function subscriptionUser(): BelongsTo
    {
        return $this->belongsTo(SubscriptionUser::class);
    }
    public function subscriptionAbility(): BelongsTo
    {
        return $this->belongsTo(SubscriptionAbility::class);
    }
}
