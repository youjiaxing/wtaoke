<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/9 14:50
 */

namespace App\Services\TbkThirdApi;

use App\Services\TbkThirdApi\Api\Api;

/**
 * Class Manager
 * @package App\Services\TbkThirdApi
 * @method false|mixed scOrderGet(\TopClient\request\TbkScOrderGetRequest $request)
 */
class Manager
{
    /**
     * @var array
     */
    protected $apis = [];

    /**
     * @param array $api
     */
    public function setApis($apis)
    {
        $this->apis = $apis;
    }

    /**
     * @param string|object $api
     *
     * @return $this
     */
    public function pushApi($api)
    {
        if (!(is_string($api) || $api instanceof Api)) {
            throw new \InvalidArgumentException("传入无效的 api 服务: ".json($api));
        }

        if (!in_array($api, $this->apis)) {
            $this->apis[] = $api;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getApis()
    {
        return $this->apis;
    }

    /**
     * 遍历调用所有注册的第三方api服务, 直到有任一成功或所有都失败.
     *
     * @param $name
     * @param $arguments
     *
     * @return false|mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists(Api::class, $name) && $name != 'getName') {
            if (empty($this->getApis())) {
                \Log::warning(__CLASS__." 未注册任何第三方api服务!");
                return false;
            }

            $first = true;
            foreach ($this->getApis() as $k => $api) {
                if (!$api instanceof Api) {
                    $api = $this->apis[$k] = app($api);
                }
                $response = call_user_func_array([$api, $name], $arguments);
//                $response = \App::call([$api, $name], $arguments);
                if ($response !== false) {
                    if (!$first) {
                        \Log::info("使用备用接口 " . $api->getName() . "::{$name} 成功.");
                    }
                    break;
                }
                $first = false;
            }

            return $response;
        }

        throw new \BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $name . '()');
    }
}