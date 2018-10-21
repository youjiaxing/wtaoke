<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 17:31
 */

namespace App\Transformers;

use App\Handlers\TbkRebateHandler;
use App\Models\TbkOrder;
use Carbon\Carbon;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Support\Str;

class TbkOrderTransformer
{
    /**
     * 新订单 - 文本消息格式
     *
     * @param TbkOrder $tbkOrder
     *
     * @return Text
     */
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
EOF;
        $msg = sprintf(
            $format,
            $user->name,
//            $tbkOrder->trade_id,
            $tbkOrder->trade_parent_id,
            Str::limit($tbkOrder->item_title, 20),
            $tbkOrder->create_time->toDateTimeString(),
            $tbkOrder->alipay_total_price,
            app(TbkRebateHandler::class)->getRebate($tbkOrder, true)
        );
        return new Text($msg);
    }

    /**
     * 新订单 - 模板消息格式
     *
     * @param TbkOrder $tbkOrder
     *
     * @return array
     */
    public function newOrderWithTemplate(TbkOrder $tbkOrder)
    {
        $data = [
            'userName' => $tbkOrder->user->name,
            'tradeId' => $tbkOrder->trade_parent_id,
            'itemTitle' => Str::limit($tbkOrder->item_title, 25),
            'time' => $tbkOrder->create_time->toDateTimeString(),
            'price' => moneyFormat($tbkOrder->alipay_total_price),
            'rebate' => moneyFormat(app(TbkRebateHandler::class)->getRebate($tbkOrder, true)),
            'debug' => '',
//            'debug' => "实际佣金(已扣除10%服务费): ¥ " .
//                moneyFormat(app(TbkRebateHandler::class)->getRebate($tbkOrder, true, null, 1)),
        ];
        return $data;
    }

    /**
     * 订单结算 - 文本消息格式
     *
     * @param TbkOrder $tbkOrder
     *
     * @return Text
     */
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

    /**
     * 订单结算 - 模板消息格式
     *
     * @param TbkOrder $tbkOrder
     *
     * @return array
     */
    public function newRebateWithTemplate(TbkOrder $tbkOrder)
    {
        $data = [
            'userName' => $tbkOrder->user->name,
            'tradeId' => $tbkOrder->trade_parent_id,
            'itemTitle' => Str::limit($tbkOrder->item_title, 25),
            'time' => $tbkOrder->rebate_time->toDateTimeString(),
            'price' => moneyFormat($tbkOrder->alipay_total_price),
            'rebate' => moneyFormat(app(TbkRebateHandler::class)->getRebate($tbkOrder, true)),
            'balance' => $tbkOrder->user->balance,
            'debug' => "",
//            'debug' => "实际佣金(已扣除10%服务费): ¥ " .
//                moneyFormat(app(TbkRebateHandler::class)->getRebate($tbkOrder, true, null, 1)),
        ];
        return $data;
    }
}