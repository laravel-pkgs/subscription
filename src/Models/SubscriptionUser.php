<?php

namespace IICN\Subscription\Models;

use App\Models\User;
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

    public static function boot()
    {
        parent::boot();

        self::created(function($model) {
            $model = $model->refresh();
            SubscriptionLog::query()->create([
                'subscription_user_id' => $model->id,
                'user_id' => $model->user_id,
                'new' => ['remaining_number' => $model->remaining_number, 'expiry_at' => $model->expiry_at],
                'old' => []
            ]);
        });

        self::updating(function($model) {
            SubscriptionLog::query()->create([
                'subscription_user_id' => $model->id,
                'user_id' => $model->user_id,
                'old' => ['remaining_number' => json_decode($model->original['remaining_number'], true), 'expiry_at' => $model->original['expiry_at']],
                'new' => ['remaining_number' => json_decode($model->attributes['remaining_number'], true), 'expiry_at' => $model->attributes['expiry_at']],
            ]);
        });

        self::deleted(function($model) {
            SubscriptionLog::query()->create([
                'subscription_user_id' => $model->id,
                'user_id' => $model->user_id,
                'old' => ['remaining_number' => $model->remaining_number, 'expiry_at' => $model->expiry_at],
                'new' => []
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
