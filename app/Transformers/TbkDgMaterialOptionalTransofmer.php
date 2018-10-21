<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/5 23:11
 */

namespace App\Transformers;


use App\Handlers\TbkRebateHandler;
use App\Services\TbkApi\TbkApiService;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;

class TbkDgMaterialOptionalTransofmer
{
    protected $topClient;

    public function __construct(TbkApiService $topClient)
    {
        $this->topClient = $topClient;
    }


    public function toWeChatText($data)
    {
//        $format = "%s\n\n💸售价: %.2f(%s)\n\n店铺: %s\n🛒30天销量: %d件\n⭐佣金比例: %.2f%%  佣金预估: %.2f 元\n💸实际花费: %.2f\n\n复制本条消息到淘宝打开购买\n⭐️️[%s]⭐️️";
        $format = <<< EOF
%s

💸售价: %.2f(%s)
店铺: %s
🛒30天销量: %d件
⭐佣金预估: %.2f 元
(Debug: 佣金比例: %.2f%% 原始佣金: %.2f)
💸实际花费: %.2f

复制本条消息到淘宝打开购买
⭐️️[%s]⭐
<a href="%s">点击查看详情</a>
EOF;
        $data['commission_rate'] = floatval($data['commission_rate']) * 0.0001;
        $data['zk_final_price'] = floatval($data['zk_final_price']);
        $data['volume'] = intval($data['volume']);

        // 优惠券优惠金额
        list($couponNeed, $couponPrice) = parseCouponInfo($data['coupon_info']);
        // 预估佣金
        $finalPrice = $data['zk_final_price'] - ($data['zk_final_price'] >= $couponNeed ? $couponPrice : 0);
        $commission = $data['commission_rate'] * $finalPrice;

        $rebate = app(TbkRebateHandler::class)->calcRebate($commission);

        // 淘口令
        $url = empty($data['coupon_share_url']) ? $data['url'] : $data['coupon_share_url'];
        $tkl = $this->topClient->tpwdCreate($url, null, $data['pict_url']);
        $resp = sprintf(
            $format,
            $data['title'],
            $data['zk_final_price'],
            $data['coupon_info'] ? "可领券" . $data['coupon_info'] : "无优惠券",
            $data['shop_title'],
            $data['volume'],
            $rebate,
            $data['commission_rate'] * 100,

            $commission,
            $finalPrice - $rebate,
            $tkl['data']['model'],
            route('wechat.tbkItem.show', [$data['num_iid']])
        );
        return new Text($resp);
    }

    /**
     * 回复图文
     *
     * @param $data
     *
     * @return News
     */
    public function toWeChatNews($data)
    {
        $data['commission_rate'] = floatval($data['commission_rate']) * 0.0001;
        $data['zk_final_price'] = floatval($data['zk_final_price']);
        $data['volume'] = intval($data['volume']);


        list($couponCond, $couponAmount) = parseCouponInfo($data['coupon_info']);

        // 预估佣金
        $finalPrice = $data['zk_final_price'] - ($data['zk_final_price'] >= $couponCond ? $couponAmount : 0);
        $commission = $data['commission_rate'] * $finalPrice;
        $rebate = app(TbkRebateHandler::class)->calcRebate($commission);

        $couponDesc = $data['coupon_info'] ?? "无";

        // Emoji图案 http://www.fhdq.net/emoji.html
        $title = <<<EOF
💖 {{价格描述}}
💎 可得佣金: ¥ {$rebate} / 件
EOF;

        $title = str_replace(
            [
                '{{价格描述}}'
            ],
            [
                $data['coupon_info'] ? "券后价: {$finalPrice} (券 ¥ {$couponAmount} )" : "价格: ¥ {$finalPrice} (无券)",
            ],
            $title
        );

        $desc = <<<EOF
🎪店铺: {$data['shop_title']}
💟商品: {$data['short_title']}
🛒30天销量: {$data['volume']}件
💰原价: {$data['zk_final_price']}
🍭隐藏券: {$couponDesc}
👍点击立即购买⚡
EOF;


        $items = [
            new NewsItem([
                'title' => $title,
                'description' => $desc,
                'url' => route('wechat.tbkItem.show', [$data['num_iid']]),
                'image' => $data['pict_url']
            ])
        ];
        $news = new News($items);
        return $news;
    }
}