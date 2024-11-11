<?php

namespace IICN\Subscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResources extends JsonResource
{
    use AdditionalTrait;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title_client ?? '',
            'duration_day' => $this->duration_day ?? 0,
            'price' => $this->price,
            'discount_percent' => $this->discount_percent ?? 0,
            'sku_code' => $this->sku_code ?? '',
            'type' => $this->type ?? '',
            'count' => $this->count ?? 0,
            'description' => $this->description_client ?? '',
            'subscriptionAbilities' => SubscriptionAbilityResources::collection($this->whenLoaded('subscriptionAbilities')),
            'priority' => $this->priority ?? 0,
        ];
    }
}
