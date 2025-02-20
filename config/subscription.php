<?php

return [
    'coupon_conditions' => [

    ],

    'guard' => null,

    'enumerations' => [
        'subscription_ability_types' => ['istikhara', 'thematicـquran'],

        'subscription_types' => ['premium', 'freemium', 'package'],
    ],


    'google' => [
        'package_name' => env("SUBSCRIPTION_GOOGLE_PACKAGE_NAME"),

        'auth_config' => [
            "project_id" => env("SUBSCRIPTION_GOOGLE_AUTH_PROJECT_ID"),
            "private_key_id" => env("SUBSCRIPTION_GOOGLE_AUTH_PRIVATE_KEY_ID"),
            "private_key" => env("SUBSCRIPTION_GOOGLE_AUTH_PRIVATE_KEY"),
            "client_email" => env("SUBSCRIPTION_GOOGLE_AUTH_CLIENT_EMAIL"),
            "client_id" => env("SUBSCRIPTION_GOOGLE_AUTH_CLIENT_ID"),
            "client_x509_cert_url" => env("SUBSCRIPTION_GOOGLE_AUTH_CLIENT_X509_CERT_URL")
        ]
    ],

    'apple' => [
        'secret' => env("SUBSCRIPTION_APPLE_SECRET"),
    ],

    'cafe_bazaar' => [
        'secret' => env("CAFEBAZAAR_API_SECRET"),
    ],


    /*
    |--------------------------------------------------------------------------
    | Response Class
    |--------------------------------------------------------------------------
    |
    | must be implement from \IICN\Subscription\SubscriptionResponse
    |
    | default is \IICN\Subscription\Services\Response\Responser::class
    |
    */

    'response_class' => \IICN\Subscription\Services\Response\Responser::class,


    'additional_resource_class' => \IICN\Subscription\Http\Resources\CollectionAdditionalTrait::class,

    /*
    |--------------------------------------------------------------------------
    | Additional Data For Transaction Purchase
    |--------------------------------------------------------------------------
    |
    | must be implement from \IICN\Subscription\TransactionalData
    |
    | default is \IICN\Subscription\Services\Transaction\TransactionalData::class
    |
    */
    'additional_data_transaction' => \IICN\Subscription\Services\Transaction\TransactionalData::class,


    "language_codes" => [
        1 => 'en',
        2 => 'ar',
        3 => 'fr',
        4 => 'ur',
        5 => 'fa',
        6 => 'id',
        7 => 'bn',
        8 => 'ms',
        9 => 'en_AU',
        10 => 'zh',
        11 => 'de',
        12 => 'tr',
        13 => 'az',
        14 => 'ru',
        15 => 'public',
    ],

    "middlewares" => [

    ]
];
