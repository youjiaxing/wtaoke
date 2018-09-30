<?php

namespace App\Http\Controllers;

use App\Services\TbkApi\TbkApiService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TopClient\request\TbkCouponGetRequest;
use TopClient\request\TbkDgItemCouponGetRequest;
use TopClient\request\TbkItemGetRequest;
use TopClient\request\TbkTpwdCreateRequest;

class TestController extends Controller
{
    /**
     * @var TbkApiService
     */
    protected $topClient;

    protected $query;

    protected $pid;
    protected $adzoneId;

    public function __construct(TbkApiService $topClient)
    {
        $this->topClient = $topClient;
//        $this->query = "一洗黑植物洗发水正品洗洗黑染发剂黑色纯自然黑男士女士黑发神器";
//        $this->query = "海康威视萤石C3A全无线电池家用手机高清监控摄像头夜视监控器";
//        $this->query = "儿童车载便携马桶宝宝移动便携式马桶小孩旅行折叠堵车应急纸便盆";
        $this->query = "乔安门铃无线家用超远距离一拖一拖二智能电子遥控门玲老人呼叫器";
    }

    /**
     * 淘宝客商品查询
     */
    public function itemGet()
    {
        dump($this->topClient->itemGet($this->query));
    }

    /**
     * 好券清单API【导购】
     */
    public function itemCouponGet()
    {
        $resp = $this->topClient->dgItemCouponGet($this->query, "1", "20");
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

    public function getTpwd()
    {
        $resp = $this->topClient->dgItemCouponGet($this->query);
//        dump($resp);
        if (empty($resp->results)) {
            dump("没有找到符合的");
            return;
        }
        foreach ($resp->results->tbk_coupon as $item) {
            dump($item);
            $tpwdResp = $this->topClient->tpwdCreate($item->coupon_click_url);
            dump($tpwdResp->data->model);
        }
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
    public function tpwdCreate()
    {
//        $url = "https://uland.taobao.com/coupon/edetail?e=3a91yz3usMkGQASttHIRqZLGDFnIV%2Bcjr0azJ3NLTteJBiHzvBsckaVF4ht750YISWnA%2BfURUxraI0LlDVfse1gY0SwCCJ3eDfqEFBOhTcxE6Q4jByxmmMAYN9QjhYVdPxthxrC1hFoML58arsWKKe1LRo38GBz3A9HagwtMFh0t%2FzOJQMDvl6zroKjs8vldxfFlZSCevAAa0iPzmm5%2BqjzSVvMPmcHNbZESW5O5pc8%3D&traceId=0b835d6915382012885987166e";

        $url = "https://uland.taobao.com/coupon/edetail?e=xpjET%2BOGhIgNfLV8niU3RwXoB%2BDaBK5LQS0Flu%2FfbSog%2BeE%2BjpQFGNpsAmOX0u61j%2F4sdtHZ%2BsJKihL9ylynrEkHUw8KI0peXiq81YKxIKir7oNy6nY12jgREosW5pIzNlwYCsgYX%2F1k38%2Bp%2BJJZdHLmv0OwyhuG18SKFGSzcuarBPpb43b%2BuO9m4T909gG2z%2BSmfA%2Fr%2Fee3WtKFAH%2F8DmuhcEU%2FqjP4&&app_pvid=0b092be415382912650208665e&ptl=floorId:2836;app_pvid:0b092be415382912650208665e;tpp_pvid:100_11.182.80.100_78597_2391538291265034960&union_lens=lensId:0bb64321_0d49_166294dee38_30d3";

        dump($this->topClient->tpwdCreate($url));
    }

    /**
     * 通用物料搜索API（导购）
     */
    public function dgMaterialOptional()
    {
        dump($this->topClient->dgMaterialOptional($this->query));
    }

    public function dynamic($method)
    {
        if (!method_exists($this, $method)) {
            throw new NotFoundHttpException(" 不存在 $method 方法");
        }

        return app()->call([$this, $method]);
    }
}
