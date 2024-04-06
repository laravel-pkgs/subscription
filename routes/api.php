<?php

Route::prefix('subscription/api/v1')->middleware(config('subscription.middlewares'))->group(function () {
    Route::namespace("IICN\Subscription\Http\Controllers")->middleware('auth.subscription')->group(function () {
        Route::namespace('Subscription')->group(function () {
            Route::get('subscriptions', 'Index');
            Route::get('subscriptions/types/{type}', 'IndexByType');
            Route::post('subscriptions/verify-purchase', 'VerifyPurchase');
        });

        Route::namespace('SubscriptionCoupon')->group(function () {
            Route::post('subscription-coupons', 'StoreWithSubscriptionCoupon');
        });
    });
});
