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
use Illuminate\Support\Facades\Redis;

class KoussApi extends Api
{
    use HasHttpRequests;

    protected $prodUrl = "http://gateway.kouss.com/tbprod/";

    protected $testUrl = "http://gateway.kouss.com/tbprod/";

    /**
     * @var string 淘宝授权码
     * @see https://oauth.taobao.com/authorize?response_type=token&client_id=23196777&state=1212&view=web 授权地址
     * @see https://kouss.com/ulandcode.html 文章地址
     */
    protected $session;

    public function __construct($session, $prod = false)
    {
        $this->session = $session;
        $this->baseUri = $prod ? $this->prodUrl : $this->testUrl;
    }

    protected function getParams()
    {
        return [
            'session' => $this->session,
        ];
    }

    public function scOrderGet(TbkScOrderGetRequest $request)
    {
        try {
            if (!Redis::set(__METHOD__, 1, "nx", "ex", 1)) {
                \Log::debug(__METHOD__ . " lock failed.");
                return false;
            }


            $data = array_merge($request->getApiParas(), $this->getParams());
            $response = $this->httpPostJson("orderGet", $data);
            $this->lastVisit = microtime(true);

            $stringBody = (string)$response->getBody();
            $content = json_decode($stringBody, true, 512, JSON_BIGINT_AS_STRING);

            if (!empty($content['code']) && $content['code'] == 106 && $content['msg'] == 'Too fast') {
                \Log::debug(__METHOD__ . " 结果失败: $stringBody");
                return false;
            }

            if (empty($content['tbk_sc_order_get_response'])) {
                \Log::warning(__METHOD__ . " 结果失败: $stringBody");
                return false;
            }
            return isset($content['tbk_sc_order_get_response']['results']['n_tbk_order']) ?
                $content['tbk_sc_order_get_response']['results']['n_tbk_order'] :
                [];
        } catch (\Exception $e) {
            \Log::warning(__METHOD__ . " 请求失败: " . $e->getMessage());
            return false;
        }
    }
}
