<?php

namespace IICN\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Modules\Core\Http\Resources\UserResource;

class SubscriptionUserResources extends JsonResource
{
    use AdditionalTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subscription_id' => $this->subscription_id,
            'remaining_number' => $this->remaining_number,
            'user' => new UserResource($this->whenLoaded('user')),
            'subscription' => new SubscriptionResources($this->whenLoaded('subscription')),
            'expiry_at' => $this->expiry_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
