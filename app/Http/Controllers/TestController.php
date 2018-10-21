<?php

namespace App\Http\Controllers;

use App\Services\TbkApi\TbkApiService;
use App\Transformers\TbkDgMaterialOptionalTransofmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestController extends Controller
{
    /**
     * @var TbkApiService
     */
    protected $topClient;

    public function __construct(TbkApiService $topClient)
    {
        $this->topClient = $topClient;
    }

    public function dynamic($method)
    {
        if (!method_exists($this, $method)) {
            throw new NotFoundHttpException(" 不存在 $method 方法");
        }

        return app()->call([$this, $method]);
    }

    /**
     * 淘宝客商品查询
     */
    public function itemGet(Request $request)
    {
        dump($this->topClient->itemGet($request['query']));
    }

    public function itemInfoGet(Request $request)
    {
        dump($this->topClient->itemInfoGet($request['query']));
    }

    /**
     * 好券清单API【导购】
     */
    public function itemCouponGet(Request $request)
    {
        $resp = $this->topClient->dgItemCouponGet(
            $request['query'],
            $request->input('pageNo', '1'),
            $request->input('pageSize', '20')
        );
        dump($resp);
        return;

        var_dump($resp);
//        $resp->results->tbk_coupon = json_decode(json_encode($resp->results->tbk_coupon), true);
        usort($resp->results->tbk_coupon, function ($a, $b) {
            return $a->volume < $b->volume;
        });
//        var_dump($resp);
        dump($resp);
    }

    /**
     * 生成淘口令
     */
    public function testWeChat(Request $request)
    {
        $resp = $this->topClient->dgMaterialOptional($request['query']);
        if (empty($resp)) {
            dump("没有找到符合的商品");
            return;
        }
        dump($resp);
        dump(app(TbkDgMaterialOptionalTransofmer::class)->toWeChatText($resp[0]));


//        $resp[0]
//        foreach ($resp->results->tbk_coupon as $item) {
//            dump($item);
//            $tpwdResp = $this->topClient->tpwdCreate($item->coupon_click_url);
//            dump($tpwdResp->data->model);
//        }
//        dump($resp->results->tbk_coupon);
    }



    /**
     * 获取优惠券
     */
    public function couponGet()
    {
        dump($this->topClient->couponGet("571167414623", "69ef0dfe1884435aa9e16e7e4f1d0834"));
    }

    /**
     * 淘宝客淘口令
     */
    public function tpwdCreate(Request $request)
    {
//        $url = "https://uland.taobao.com/coupon/edetail?e=3a91yz3usMkGQASttHIRqZLGDFnIV%2Bcjr0azJ3NLTteJBiHzvBsckaVF4ht750YISWnA%2BfURUxraI0LlDVfse1gY0SwCCJ3eDfqEFBOhTcxE6Q4jByxmmMAYN9QjhYVdPxthxrC1hFoML58arsWKKe1LRo38GBz3A9HagwtMFh0t%2FzOJQMDvl6zroKjs8vldxfFlZSCevAAa0iPzmm5%2BqjzSVvMPmcHNbZESW5O5pc8%3D&traceId=0b835d6915382012885987166e";
        dump($this->topClient->tpwdCreate($request['url'], $request->input('text', '<内部优惠通道>')));
    }

    /**
     * 通用物料搜索API（导购）
     */
    public function dgMaterialOptional(Request $request)
    {
        dump($this->topClient->dgMaterialOptional($request['query']));
//        var_dump($this->topClient->dgMaterialOptional($request['query']));
    }

    public function weChatMenu(\EasyWeChat\OfficialAccount\Application $app)
    {
        $buttons = [
            [
                "type" => "view",
                "name" => "个人中心",
                "url" => "http://wtaoke.laraphp.cn/wechat/user"
            ],
        ];
        $resp = $app->menu->create($buttons);
        dump($resp);

        $list = $app->menu->list();
        dump($list);

//        $list = $app->menu->list();
//        dump($list);

//        $current = $app->menu->current();
//        dump($current);

//        $buttons = [
//            [
//                "type" => "click",
//                "name" => "今日歌曲",
//                "key" => "V1001_TODAY_MUSIC"
//            ],
//            [
//                "name" => "菜单",
//                "sub_button" => [
//                    [
//                        "type" => "view",
//                        "name" => "搜索",
//                        "url" => "http://www.soso.com/"
//                    ],
//                    [
//                        "type" => "view",
//                        "name" => "视频",
//                        "url" => "http://v.qq.com/"
//                    ],
//                    [
//                        "type" => "click",
//                        "name" => "赞一下我们",
//                        "key" => "V1001_GOOD"
//                    ],
//                ],
//            ],
//        ];
//        $app->menu->create($buttons);
    }
}
