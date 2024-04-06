<?php

namespace IICN\Subscription\Http\Requests;

use Carbon\Carbon;
use IICN\Subscription\CouponConditionInterface;
use IICN\Subscription\Models\SubscriptionCoupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class VerifyPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard(config('subscription.guard'))->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gateway' => 'required|in:appStore,playStore',
            'skuCode' => 'required|string|max:100|exists:subscriptions,sku_code',
            'purchaseToken' => 'required|string',
            'orderId' => 'required|string|max:250',
            'price' => 'nullable|string',
        ];
    }
}
