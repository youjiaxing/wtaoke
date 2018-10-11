<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 16:07
 */

namespace App\Handlers;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChat\Kernel\Messages\Message;

class WeChatNotify
{
    /**
     * @var \EasyWeChat\OfficialAccount\Application
     */
    protected $app;

    public function __construct(\EasyWeChat\OfficialAccount\Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param string  $openId
     * @param Message $msg
     */
    public function notifyUser($openId, Message $msg)
    {
        $app = \EasyWeChat::officialAccount();
        try {
            $app->customer_service->message($msg)->to($openId)->send();
        } catch (\Exception $e) {
            \Log::warning("通知用户 $openId 失败: ".$e->getMessage());
        }
    }
}
