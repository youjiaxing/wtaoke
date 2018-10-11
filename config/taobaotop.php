<?php
/*
 * This file is part of Laravel Taobao Top Client.
 *
 * (c) orzcc <orzcczh@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
return [

    'default' => 'app',

    'connections' => [
        'app' => [
            'app_key' => env('TAOBAO_APP_KEY', 'APP KEY'),
            'app_secret' => env('TAOBAO_APP_SECRET', 'APP SECRET'),
            'format' => 'json',
            'adzoneId' => env('TAOBAO_ADZON_ID', null),
        ]
    ],

    'adzoneId' => env('TAOBAO_ADZON_ID', null),

    'third' => [
        'kouss' => [
            'session' => env('KOUSS_SESSION', ''),
            'debug' => env('KOUSS_DEBUG', true),
        ]
    ],

    // 单次查询失败重试次数
    'order_get' => [
        'fail_retry' => [
            'interval' => 3,
            'count' => 5,
        ],

        'interval' => 3,
    ],
];
