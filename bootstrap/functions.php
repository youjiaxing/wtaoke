<?php

function json($obj, $option = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
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

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function moneyFormat($price)
{
    return number_format($price, 2, '.', '');
}

function orderStatus($tkStatus, $isRebate)
{
    switch ($tkStatus) {
        // 已结算
        case 3:
            return $isRebate ? ["success", "已结算"] : ["info", "待结算"];
            break;

        // 已付款
        case 12:
            return ["info", "已付款"];
            break;

        // 已失效
        case 13:
            return ["danger", "已失效"];
            break;

        // 已生效
        case 14:
            return ["warning", "异常订单"];
            break;

        default:
            return ["danger", "未知 $tkStatus"];
    }
}

function flowSubType($subType)
{
    switch ($subType) {
        case 11:
            return "结算";

        case 21:
            return "提现";

        default:
            return "未知 $subType";
    }
}

/**
 * 解析优惠券文本信息
 *
 * @param string $couponInfo "满100元减3元"
 *
 * @return array [$couponCond, $couponAmount]
 */
function parseCouponInfo($couponInfo)
{
    // 优惠券优惠金额
    if (preg_match("~满(\d+(?:.\d+)?)元减(\d+(?:.\d+)?)元~", $couponInfo, $matches)) {
        array_shift($matches);
        array_map('floatval', $matches);
        array_map('moneyFormat', $matches);
        list($couponNeed, $couponPrice) = $matches;
    } else {
        $couponNeed = 0;
        $couponPrice = 0;
    }
    return [
        $couponNeed,
        $couponPrice
    ];
}