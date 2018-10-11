<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 17:31
 */

namespace App\Transformers;

use App\Models\TbkOrder;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Str;

class TbkOrderTransformer
{
    public function newOrderWithText(TbkOrder $tbkOrder)
    {
        $user = $tbkOrder->user;
        $format = <<< EOF
%s, 下单成功
--------------------------------
订单号: %s
商品名: %s
下单时间: %s
实付金额: %.2f 元
--------------------------------
预估收益: %.2f 元
↑ 不含服务费(10%%)
EOF;
        $msg = sprintf(
            $format,
            $user->name,
//            $tbkOrder->trade_id,
            $tbkOrder->trade_parent_id,
            Str::limit($tbkOrder->item_title, 20),
            $tbkOrder->create_time->toDateTimeString(),
            $tbkOrder->alipay_total_price,
            $tbkOrder->pub_share_pre_fee
        );
        return new Text($msg);
    }

    public function newRebateWithText(TbkOrder $tbkOrder)
    {
        $user = $tbkOrder->user;
        $format = <<< EOF
%s, 您有一笔订单已确认收货
--------------------------------
订单号: %s
商品名: %s
结算时间: %s
实付金额: %.2f 元
--------------------------------
佣金: %.2f 元 已入账
↑ 已扣除服务费(10%%)
EOF;
        $msg = sprintf(
            $format,
            $user->name,
//            $tbkOrder->trade_id,
            $tbkOrder->trade_parent_id,
            Str::limit($tbkOrder->item_title, 20),
            $tbkOrder->earning_time->toDateTimeString(),
            $tbkOrder->alipay_total_price,
            $tbkOrder->rebate_fee
        );
        return new Text($msg);
    }
}