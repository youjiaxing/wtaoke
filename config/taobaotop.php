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

    // 备用推广位
    'adzoneId' => env('TAOBAO_ADZON_ID', null),

    // 第三方订单查询服务
    'third' => [
        // Kouss https://www.yuque.com/kouss/taoke/ngffqg
        'kouss' => [
            'session' => env('KOUSS_SESSION', ''),
            'debug' => env('KOUSS_DEBUG', true),
        ],

        // 喵有券 http://open.21ds.cn/index/index/openapi/id/4.shtml?ptype=1
        'miao_you_quan' => [
            'app_key' => env('MIAO_YOU_QUAN_APP_KEY'),
            'tb_name' => env('MIAO_YOU_QUAN_TB_NAME'),
        ]
    ],

    // 消息通知方式
    'notify_type' => 'template',    // 'template', 'text'

    // 单次查询失败重试次数
    'order_get' => [
        'fail_retry' => [
            'interval' => 3,
            'count' => 8,
        ],

        'interval' => 5,
    ],

    // 阿里的服务费
    "service_fee_rate" => 0.1,
    // 用户佣金比率
    "user_share_rate" => 0.9,
];
