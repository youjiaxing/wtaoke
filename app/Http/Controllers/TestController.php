<?php

namespace App\Http\Controllers;

use App\Services\TbkApi\TbkApiService;
use App\Transformers\TbkDgMaterialOptionalTransofmer;
use Illuminate\Http\Request;
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

    /**
     * 淘宝客商品查询
     */
    public function itemGet(Request $request)
    {
        dump($this->topClient->itemGet($request['query']));
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
        if (empty($resp) || empty($resp->result_list)) {
            dump("没有找到符合的商品");
            return;
        }
        dump($resp);
        dump(app(TbkDgMaterialOptionalTransofmer::class)->toWeChatText(($resp->result_list->map_data)[0]));


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

    public function dynamic($method)
    {
        if (!method_exists($this, $method)) {
            throw new NotFoundHttpException(" 不存在 $method 方法");
        }

        return app()->call([$this, $method]);
    }
}
