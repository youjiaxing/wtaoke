<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/5 23:11
 */

namespace App\Transformers;


use App\Services\TbkApi\TbkApiService;

class TbkDgMaterialOptionalTransofmer
{
    protected $topClient;

    public function __construct(TbkApiService $topClient)
    {
        $this->topClient = $topClient;
    }


    public function toWeChatText($data)
    {
        $format = "%s\n\n💸售价: %.2f(%s)\n\n店铺: %s\n🛒30天销量: %d件\n⭐佣金比例: %.2f%%  佣金预估: %.2f 元\n💸实际花费: %.2f\n\n复制本条消息到淘宝打开购买\n⭐️️[%s]⭐️️";

        $data->commission_rate = floatval($data->commission_rate) * 0.0001;
        $data->zk_final_price = floatval($data->zk_final_price);
        $data->volume = intval($data->volume);

        // 优惠券优惠金额
        if (preg_match("~满(\d+(?:.\d+)?)元减(\d+(?:.\d+)?)元~", $data->coupon_info, $matches)) {
            array_shift($matches);
            array_map('floatval', $matches);
            list($couponNeed, $couponPrice) = $matches;
        } else {
            $couponNeed = 0;
            $couponPrice = 0;
        }
        // 预估佣金
        $finalPrice = $data->zk_final_price - ($data->zk_final_price >= $couponNeed ? $couponPrice : 0);
        $commission = $data->commission_rate * $finalPrice;
        // 淘口令
        $url = empty($data->coupon_share_url) ? $data->url : $data->coupon_share_url;
        $tkl = $this->topClient->tpwdCreate($url, null, $data->pict_url);
        $resp = sprintf(
            $format,
            $data->title,
            $data->zk_final_price,
            $data->coupon_info ? "可领券" . $data->coupon_info : "无优惠券",
            $data->shop_title,
            $data->volume,
            $data->commission_rate * 100,
            $commission,
            $finalPrice - $commission,
            $tkl->data->model
        );
        return new \EasyWeChat\Kernel\Messages\Text($resp);
    }
}