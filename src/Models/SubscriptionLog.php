<?php

namespace IICN\Subscription\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionLog extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['subscription_user_id', 'user_id', 'new', 'old'];

    public function subscriptionUser(): BelongsTo
    {
        return $this->belongsTo(SubscriptionUser::class);
    }
}
