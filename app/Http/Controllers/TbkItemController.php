<?php

namespace App\Http\Controllers;

use App\Handlers\TbkRebateHandler;
use App\Models\TbkAdzone;
use App\Services\TbkApi\TbkApiService;
use App\Services\TbkThirdApi\Api\KoussApi;
use App\Services\TbkThirdApi\Api\MiaoYouQuanApi;
use Illuminate\Http\Request;
use TopClient\request\TbkPrivilegeGetRequest;

class TbkItemController extends Controller
{
    public function show($itemId)
    {
        // 查询优惠信息
        $adzoneId = \Auth::user()->tbk_adzone_id;
        $siteId = config('taobaotop.siteId');
        $userId = config('taobaotop.userId');
        $pid = "mm_{$userId}_{$siteId}_{$adzoneId}";
        // 高佣转链接API(商品ID)
        //TODO 此处加个缓存 key: "{pid}_{itemId}"
        $cacheKey1 = 'getItemGYUrl_' . $itemId . '_' . $pid;
        $resp = \Cache::remember($cacheKey1, 10, function () use ($itemId, $pid) {
            return app(MiaoYouQuanApi::class)->getItemGYUrl($itemId, $pid);
        });
        if (empty($resp['tpwd'])) {
            \Cache::delete($cacheKey1);
            return "<h1>!! 生成淘口令无效, 请联系管理员 !!</h1>";
        }


        // 查询商品基本信息
        $itemInfo = \Cache::remember('itemInfoGet_' . $itemId, 10, function () use ($itemId) {
            return app(TbkApiService::class)->itemInfoGet($itemId)[0];
        });


        $data = [
            'has_coupon' => $resp['has_coupon'],    // bool
            'coupon_info' => $resp['coupon_info'] ?? "",    // "满148元减100元"
            'tpwd' => $resp['tpwd'], // 淘口令
            'max_commission_rate' => floatval($resp['max_commission_rate']) * 0.01, // 佣金比率


            'pict_url' => $itemInfo['pict_url'],    // 主图
            'user_type' => $itemInfo['user_type'], //卖家类型，0表示集市，1表示商城
            'nick' => $itemInfo['nick'], //店铺名称
            'price' => floatval($itemInfo['zk_final_price']), // 商品原价
            'volume' => $itemInfo['volume'],    // 30天销量
            'title' => $itemInfo['title'], // 商品标题
        ];

        if ($data['has_coupon']) {
            // 优惠券优惠金额
            if (preg_match("~满(\d+(?:.\d+)?)元减(\d+(?:.\d+)?)元~", $data['coupon_info'], $matches)) {
                array_shift($matches);
                array_map('floatval', $matches);
                list($couponNeed, $couponPrice) = $matches;
                if ($data['price'] > $couponNeed) {
                    // 优惠券面额
                    $data['coupon'] = $couponPrice;
                    // 最终价格
                    $data['final_price'] = $data['price'] > $couponNeed ?
                        $data['price'] - $couponPrice :
                        $data['price'];
                }
            }
        } else {
            $data['coupon'] = 0;
            // 最终价格
            $data['final_price'] = $data['price'];
        }

        // 预估佣金
        $rawRebate = $data['final_price'] * $data['max_commission_rate'];
        $data['rebate'] = app(TbkRebateHandler::class)->calcRebate($rawRebate);

        return view('tbk_items.show', compact('data'));
    }
}
