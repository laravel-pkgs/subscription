<?php

Route::prefix('subscription/api/v1')->middleware(config('subscription.middlewares'))->group(function () {
    Route::namespace("IICN\Subscription\Http\Controllers")->group(function () {
        Route::namespace('Subscription')->middleware('auth.subscription')->group(function () {
            Route::get('subscriptions', 'Index');
            Route::get('subscriptions/types/{type}', 'IndexByType');
            Route::post('subscriptions/verify-purchase', 'VerifyPurchase');

            Route::post('subscriptions/verify-subscription-purchase', 'VerifyPurchase@verifyPurchaseSubscription');
        });

        Route::namespace('Subscription')->group(function () {
            Route::get('app-subscriptions', 'Index@indexSubscriptions');
        });

        Route::namespace('SubscriptionCoupon')->middleware('auth.subscription')->group(function () {
            Route::post('subscription-coupons', 'StoreWithSubscriptionCoupon');
        });

        Route::namespace('SubscriptionAdmin')->middleware(['role:' . \Modules\Core\Enums\PermissionsEnum::Management->value], 'auth.api_or_passport')->group(function () {
            Route::get('subscription-users', 'SubscriptionController@userSubscriptions');
        });
    });
});
