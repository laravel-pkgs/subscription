<?php

namespace IICN\Subscription\Http\Requests;

use Carbon\Carbon;
use IICN\Subscription\CouponConditionInterface;
use IICN\Subscription\Models\SubscriptionCoupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreWithSubscriptionCouponRequest extends FormRequest
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
            'code' => 'required|string|max:100',
            'subscription_coupon_id' => 'required',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function prepareForValidation()
    {
        if (!isset($this->code)) {
            return true;
        }

        $subscriptionCoupon = SubscriptionCoupon::query()
            ->where('code', $this->code)
            ->where('expiry_at', '>=', Carbon::now())
            ->where('count', '>', 0)
            ->first();

        if ($subscriptionCoupon) {
            $this->merge(['subscription_coupon_id' => $subscriptionCoupon->id]);
        } else {
            return throw ValidationException::withMessages(['code' => 'code not found!']);
        }

        $conditions = config('subscription.coupon_conditions');

        foreach ($conditions as $condition) {
            if (app($condition) instanceof CouponConditionInterface) {
                $classCondition = new $condition();
                if (!$classCondition->handle($subscriptionCoupon)) {
                    return throw ValidationException::withMessages(['message' => $classCondition->failedMessage()]);
                }
            }
        }
    }
}
