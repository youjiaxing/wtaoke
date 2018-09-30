<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/9/29 14:12
 */
namespace App\Services\TbkApi;

use TopClient\TopClient;
use TopClient\request\TbkCouponGetRequest;
use TopClient\request\TbkDgItemCouponGetRequest;
use TopClient\request\TbkItemGetRequest;
use TopClient\request\TbkTpwdCreateRequest;

class TbkApiService
{
    /**
     * @var \TopClient\TopClient
     */
    protected $topClient;

    protected $adzonId;

    public function __construct(TopClient $topClient)
    {
        $this->topClient = $topClient;
    }

    /**
     * @return mixed
     */
    public function getAdzonId()
    {
        return $this->adzonId;
    }

    /**
     * @param $adzonId
     *
     * @return $this
     */
    public function setAdzonId($adzonId)
    {
        $this->adzonId = $adzonId;
        return $this;
    }

    /**
     * 淘宝客商品查询
     *
     * @see http://open.taobao.com/api.htm?cid=1&docId=24515&docType=2
     *
     * @param $query
     *
     * @return mixed|\SimpleXMLElement|\TopClient\ResultSet
     */
    public function itemGet($query)
    {
        $req = new TbkItemGetRequest;
        $req->setFields("num_iid,title,pict_url,reserve_price,zk_final_price,user_type,provcity,item_url");
        $req->setQ($query);
        $req->setSort("tk_total_sales");
        $req->setPlatform('2');
        $req->setPageNo('1'); // 实验后发现必需用字符串的数字才能正确分页
        $req->setPageSize('40');
        return $resp = $this->topClient->execute($req);
    }

    /**
     * 好券清单API【导购】
     *
     * @see http://open.taobao.com/api.htm?cid=1&docId=29821&docType=2
     *
     * @param $query
     *
     * @return mixed|\SimpleXMLElement|\TopClient\ResultSet
     */
    public function dgItemCouponGet($query, $pageNo = "1", $pageSize = "2")
    {
        $req = new TbkDgItemCouponGetRequest;
//        $req->setAdzoneId("34336250141");
        $req->setAdzoneId($this->adzonId);
//        $req->setCat("16,18");
        $req->setPlatform('2');
        $req->setQ($query);
        $req->setPageNo($pageNo);
        $req->setPageSize($pageSize);
        return $resp = $this->topClient->execute($req);
    }

    /**
     * 阿里妈妈推广券信息查询。传入商品ID+券ID，或者传入me参数，均可查询券信息。
     *
     * @see http://open.taobao.com/api.htm?cid=1&docId=31106&docType=2
     *
     * @param $itemId
     * @param $couponId
     *
     * @return mixed|\SimpleXMLElement|\TopClient\ResultSet
     */
    public function couponGet($itemId, $couponId)
    {
        $req = new TbkCouponGetRequest;
        $req->setItemId($itemId);
        $req->setActivityId($couponId);
        return $this->topClient->execute($req);
    }

    /**
     * 淘宝客淘口令
     *
     * @see http://open.taobao.com/api.htm?docId=31127&docType=2
     *
     * @param $couponUrl
     *
     * @return mixed|\SimpleXMLElement|\TopClient\ResultSet
     */
    public function tpwdCreate($couponUrl, $text = null, $logoUrl = null)
    {
        $req = new TbkTpwdCreateRequest;
//        $req->setUserId("123");
        $req->setText($text);
        $req->setUrl($couponUrl);
        $req->setLogo($logoUrl);
        $req->setExt("{}");
        return $resp = $this->topClient->execute($req);
    }

    /**
     * 通用物料搜索API（导购）
     *
     * @see http://open.taobao.com/api.htm?cid=1&docId=35896&docType=2
     *
     * @param $query
     * @param $adzoneId
     *
     * @return mixed|\SimpleXMLElement|\TopClient\ResultSet
     */
    public function dgMaterialOptional($query)
    {
        $req = new \TopClient\request\TbkDgMaterialOptionalRequest();
//        $req = new \App\Services\TbkApi\TbkDgMaterialOptionalRequest();
//        $req->setStartDsr("10");
        $req->setPageSize("20");
        $req->setPageNo("1");
        $req->setPlatform("2");
//        $req->setEndTkRate("1234");
//        $req->setStartTkRate("1234");
//        $req->setEndPrice("10");
//        $req->setStartPrice("10");
//        $req->setIsOverseas("false");
//        $req->setIsTmall("false");
        $req->setSort("total_sales_des");
//        $req->setItemloc("杭州");
//        $req->setCat("16,18");
        $req->setQ($query);
//        $req->setMaterialId("2836");
//        $req->setHasCoupon("false");
//        $req->setIp("13.2.33.4");
        $req->setAdzoneId($this->adzonId);
//        $req->setNeedFreeShipment("true");
//        $req->setNeedPrepay("true");
//        $req->setIncludePayRate30("true");
//        $req->setIncludeGoodRate("true");
//        $req->setIncludeRfdRate("true");
//        $req->setNpxLevel("2");
//        $req->setEndKaTkRate("1234");
//        $req->setStartKaTkRate("1234");
//        $req->setDeviceEncrypt("MD5");
//        $req->setDeviceValue("xxx");
//        $req->setDeviceType("IMEI");
        return $resp = $this->execute($req);
    }


    protected function execute($request)
    {
        return $this->topClient->execute($request);
    }
}