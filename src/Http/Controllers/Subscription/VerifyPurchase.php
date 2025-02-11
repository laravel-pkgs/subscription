<?php

namespace IICN\Subscription\Http\Controllers\Subscription;

use IICN\Subscription\Constants\Status;
use IICN\Subscription\Http\Controllers\Controller;
use IICN\Subscription\Http\Requests\VerifyPurchaseRequest;
use IICN\Subscription\Models\SubscriptionLog;
use IICN\Subscription\Models\SubscriptionTransaction;
use IICN\Subscription\Services\Purchase\Appstore;
use IICN\Subscription\Services\Purchase\AppStoreSubscription;
use IICN\Subscription\Services\Purchase\CafeBazaar;
use IICN\Subscription\Services\Purchase\CafeBazaarSubscription;
use IICN\Subscription\Services\Purchase\Playstore;
use IICN\Subscription\Services\Purchase\PlayStoreSubscription;
use IICN\Subscription\Services\Purchase\Purchase;
use IICN\Subscription\Services\Response\SubscriptionResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Auth;

class VerifyPurchase extends Controller
{
    /**
     * @OA\Post(
     *     path="/subscription/api/v1/subscriptions/verify-purchase",
     *     operationId="verifyPurchase",
     *     tags={"Subscription"},
     *     summary="verify purchase",
     *     description="verify purchase",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguageHeader"),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Requirements",
     *         @OA\JsonContent(
     *             required={"gateway", "skuCode", "purchaseToken", "orderId"},
     *             @OA\Property(
     *                 property="gateway",
     *                 type="string",
     *                 example="appStore OR playStore"
     *             ),
     *             @OA\Property(
     *                 property="skuCode",
     *                 type="string",
     *                 description="sku_code of subscription"
     *             ),
     *             @OA\Property(
     *                 property="purchaseToken",
     *                 type="string",
     *                 description="purchaseToken of store"
     *             ),
     *             @OA\Property(
     *                 property="orderId",
     *                 type="string",
     *                 description="orderId of store"
     *             ),
     *             @OA\Property(
     *                 property="price",
     *                 type="string",
     *                 example="9.9 $"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description=""
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function __invoke(VerifyPurchaseRequest $request)
    {
        SubscriptionLog::query()->create([
            'user_id' => Auth::guard(config('subscription.guard'))->id(),
            'new' => ['body' => $request->validated(), 'headers' => $request->headers->all()],
        ]);

        $lock = Cache::lock('transaction_order_id_' . $request->validated('orderId'), 10);

        try {
            $lock->block(5);

            $subscriptionTransaction = SubscriptionTransaction::query()->where('order_id', $request->validated('orderId'))->first();

            if ($subscriptionTransaction) {
                $message = $this->getMessage($subscriptionTransaction);
                return SubscriptionResponse::data(['purchase_status' => $subscriptionTransaction->status], $message);
            }

            if ($request->gateway == 'appStore') {
                $playstore = new Purchase(new Appstore());
            } elseif($request->gateway == 'playStore') {
                $playstore = new Purchase(new Playstore());
            } elseif($request->gateway == 'cafeBazaar') {
                $playstore = new Purchase(new CafeBazaar());
            }  else {
                return SubscriptionResponse::data(['purchase_status' => Status::FAILED], trans('subscription::messages.payment_not_valid'));
            }

            $result = $playstore->verify($request->skuCode, $request->purchaseToken, $request->orderId, $request->price);

            $message = $this->getMessage($result['transaction']);

            return SubscriptionResponse::data(['purchase_status' => $result['transaction']->status], $message);
        } catch (LockTimeoutException $e) {
            return SubscriptionResponse::data(['purchase_status' => Status::FAILED], trans('subscription::messages.payment_not_valid'));
        } finally {
            $lock?->release();
        }
    }



    public function verifyPurchaseSubscription()
    {
        $attribute = request()->validate([
            'gateway' => 'required|in:appStore,playStore,cafeBazaar',
            'skuCode' => 'required|string|max:100',
            'purchaseToken' => 'required|string',
            'orderId' => 'required|string|max:250',
            'price' => 'nullable|string',
        ]);

        SubscriptionLog::query()->create([
            'user_id' => Auth::guard(config('subscription.guard'))->id(),
            'new' => ['body' => $attribute, 'headers' => request()->headers->all()],
        ]);

        $lock = Cache::lock('transaction_order_id_' . $attribute['orderId'], 10);

        try {
            $lock->block(5);

            $subscriptionTransaction = SubscriptionTransaction::query()->where('order_id', $attribute['orderId'])->first();

            if ($subscriptionTransaction) {
                $message = $this->getMessage($subscriptionTransaction);
                return SubscriptionResponse::data(['purchase_status' => $subscriptionTransaction->status], $message);
            }


            if($attribute['gateway'] == 'playStore') {
                $playstore = new Purchase(new PlayStoreSubscription());
            } elseif ($attribute['gateway'] == 'appStore') {

                $playstore = new Purchase(new AppStoreSubscription());
            } elseif ($attribute['gateway'] == 'cafeBazaar') {
                $playstore = new Purchase(new CafeBazaarSubscription());
            } else {
                return response()->json([
                    'message' => '',
                    'data' => ['purchase_status' => $subscriptionTransaction->status],
                    'status' => 1
                ], 500);
            }

            $result = $playstore->verifySubscription($attribute['skuCode'], $attribute['purchaseToken'], $attribute['orderId'], 0);

            $message = $this->getMessage($result['transaction']);


            return response()->json([
                'message' => $message,
                'data' => ['purchase_status' => $result['transaction']->status],
                'status' => 1
            ], 200);
            // return SubscriptionResponse::data(['purchase_status' => $result['transaction']->status], $message);
        } catch (LockTimeoutException $e) {
            return response()->json([
                'message' => 'payment failed',
                'data' => ['purchase_status' => Status::FAILED],
                'status' => 0
            ], 500);
            // return SubscriptionResponse::data(['purchase_status' => Status::FAILED], trans('subscription::messages.payment_not_valid'));
        } finally {
            $lock?->release();
        }

    }


    private function getMessage($transaction): string
    {
        if($transaction->status == Status::SUCCESS) {
            $message = trans('subscription::messages.payment_done');
        } elseif ($transaction->status == Status::FAILED) {
            $message = trans('subscription::messages.payment_not_valid');
        } elseif ($transaction->status == Status::PENDING) {
            $message = trans('subscription::messages.payment_pending');
        } else {
            $message = trans('subscription::messages.payment_not_valid');
        }
        return $message;
    }
}
