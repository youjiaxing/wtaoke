<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/9/29 20:20
 */
namespace App\Transformers;

class TbkWeChatTransform
{
    /**
     * 转化为文本淘口令
     *
     * @param $data
     *
     * @return \EasyWeChat\Kernel\Messages\Message
     */
    public function toTklText($data)
    {
        $format = "%s\n\n💸售价: %.2f(%s后)\n\n店铺: %s\n🛒30天销量: %d件\n⭐佣金比例: %.2f%%  预估: %.2f 元\n💸实际花费: %.2f\n\n复制本条消息到淘宝打开购买\n⭐️️[%s]⭐️️";

        $commission = floatval($data->commission_rate) * 0.0001 * floatval($data->zk_final_price);
        $resp = sprintf(
            $format,
            $data->title,
            floatval($data->zk_final_price),
            $data->coupon_info,
            $data->shop_title,
            intval($data->volume),
            floatval($data->commission_rate),
            $commission,
            floatval($data->zk_final_price) - $commission,
            $data->model
        );
        return new \EasyWeChat\Kernel\Messages\Text($resp);
    }
}