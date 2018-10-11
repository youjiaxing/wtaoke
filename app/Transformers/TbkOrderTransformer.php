<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 17:31
 */

namespace App\Transformers;

use App\Models\TbkOrder;
use EasyWeChat\Kernel\Messages\Text;

class TbkOrderTransformer
{
    public function toWeChatText(TbkOrder $tbkOrder)
    {
        $user = $tbkOrder->user;
        $format = <<< EOF
%s, 下单成功
--------------------------------
订单号: %s
父订单号: %s
下单时间: %s
实付金额: %.2f 元
--------------------------------
预估收益: %.2f 元
↑ 不含服务费(10%%)
EOF;
        $msg = sprintf(
            $format,
            $user->name,
            $tbkOrder->trade_id,
            $tbkOrder->trade_parent_id,
            $tbkOrder->create_time->toDateTimeString(),
            $tbkOrder->alipay_total_price,
            $tbkOrder->pub_share_pre_fee
        );
        return new Text($msg);
    }
}