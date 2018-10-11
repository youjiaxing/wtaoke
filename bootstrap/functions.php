<?php

function json($obj, $option = JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
{
    return json_encode($obj, $option);
}

function tbkOrderStatusMap($status)
{
    $map = [
        3 => "订单结算",
        12 => "订单付款",
        13 => "订单失效",
        14 => "订单成功",
    ];
    return $map[$status] ?? "unknown";
}
