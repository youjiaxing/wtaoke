<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/9 15:01
 */

namespace App\Services\TbkThirdApi\Api;

use App\Services\TbkThirdApi\Traits\HasHttpRequests;
use GuzzleHttp\Exception\GuzzleException;
use TopClient\request\TbkScOrderGetRequest;

class MiaoYouQuanApi extends Api
{
    use HasHttpRequests;

    /**
     * @var string 淘宝授权码
     * @see http://open.21ds.cn/user/oauth/taobao.shtml 授权地址
     * @see http://open.21ds.cn/index/index/openapi/id/4.shtml?ptype=1 文章地址
     */
    protected $appkey;

    /**
     * @var string 授权后的淘宝名称
     */
    protected $tbName;

    /**
     * @var int
     */
    protected $vipLv;

    public function __construct($appkey, $tbName, $url = null, $vipLv = 0)
    {
        $this->appkey = $appkey;
        $this->tbName = $tbName;
        $this->vipLv = intval($vipLv);
        $this->baseUri = $url ?: "https://api.open.21ds.cn/apiv1/";
    }

    protected function getParams()
    {
        return [
            'apkey' => $this->appkey,
            'tbname' => $this->tbName,
        ];
    }

    /**
     * 限制VIP=1
     *
     * @param TbkScOrderGetRequest $request
     *
     * @return array|bool
     */
    public function scOrderGet(TbkScOrderGetRequest $request)
    {
        if ($this->vipLv <= 1) {
            return false;
        }

//        $req = new TbkScOrderGetRequest();
//        $req->setSpan(1200);
//        $req->setPageSize("100");
//        $req->setPageNo("1");
//        $req->setTkStatus("1");
//        $req->setOrderQueryType("create_time");
//        $req->setStartTime("2018-10-12 11:40:00");
//        $request = $req;

        try {
            $data = [];
            // 这个接口的请求参数跟官方不同, 需要转换
            foreach ($request->getApiParas() as $k => $v) {
                $data[str_replace('_', '', $k)] = $v;
            }
            foreach ([
                         'page' => 'pageno',
                         'ordertype' => 'orderquerytype',
                     ] as $k1 => $k2) {
                if (!isset($data[$k2])) {
                    continue;
                }
                $data[$k1] = $data[$k2];
                unset($data[$k2]);
            }
            $data = array_merge($data, $this->getParams());
            $data['starttime'] = urlencode($data['starttime']);
//            \Log::info("参数", $data);

            $response = $this->httpGet("gettkorder", $data);

            $stringBody = (string)$response->getBody();
            $content = json_decode($stringBody, true, 512, JSON_BIGINT_AS_STRING);

            if (empty($content['code']) || $content['code'] != 200) {
                if ($content['code'] == -1 && $content['msg'] == "本时间内无订单") {
                    return [];
                }

                \Log::warning(__METHOD__ . " 结果失败: " . json($content));
                return false;
            }

            return isset($content['data']['n_tbk_order']) ?
                (isset($content['data']['n_tbk_order']['adzone_id']) ? [$content['data']['n_tbk_order']] : $content['data']['n_tbk_order']) :
                [];
        } catch (GuzzleException $e) {
            \Log::warning(__METHOD__ . " 请求失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 高佣转链接API(商品ID)
     *
     * @see https://open.21ds.cn/index/index/openapi/id/1.shtml?ptype=1
     */
    public function getItemGYUrl($itemId, $pid)
    {
        $url = 'getitemgyurl';
        try {
            $data = [
                'pid' => $pid,
                'itemid' => $itemId,
                'tpwd' => 1,
//                'shorturl' => 1,
            ];
            $content = $this->execute($url, $data);

            if (empty($content['code']) || $content['code'] != 200) {
                \Log::warning(__METHOD__ . " 结果失败: " . json($content));
                return false;
            }

            return $content['result']['data'];
        } catch (\Exception $e) {
            \Log::warning(__METHOD__ . " 请求失败: " . $e->getMessage());
            return false;
        }
    }

    public function execute($url, $data)
    {
        try {
            $data = array_merge($data, $this->getParams());
            $response = $this->httpGet($url, $data);

            $stringBody = (string)$response->getBody();
            $content = json_decode($stringBody, true, 512, JSON_BIGINT_AS_STRING);
            return $content;
        } catch (\Exception $e) {
            \Log::warning(__METHOD__ . " 请求失败: " . $e->getMessage());
            return false;
        }
    }
}
