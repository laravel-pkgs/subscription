<?php

namespace IICN\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionTransaction extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'price',
        'price_unit',
        'subscription_id',
        'subscription_user_id',
        'subscription_coupon_id',
        'agent_type',
        'purchase_token',
        'order_id',
        'product_id',
        'additional_data',
        'response_data',
        'status'
    ];

    protected $casts = [
        'additional_data' => 'json',
        'response_data' => 'json',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function subscriptionUser(): BelongsTo
    {
        return $this->belongsTo(SubscriptionUser::class);
    }

    public function subscriptionCoupon(): BelongsTo
    {
        return $this->belongsTo(SubscriptionCoupon::class);
    }
}
