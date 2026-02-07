<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'merchant_users',
    ],

    'guards' => [
        'admin' => [            'driver' => 'session',            'provider' => 'admins',        ],        'web' => [
            'driver' => 'session',
            'provider' => 'merchant_users',
        ],
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'merchant_users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'merchant_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\MerchantUser::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
    ],

    'passwords' => [
        'merchant_users' => [
            'provider' => 'merchant_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
