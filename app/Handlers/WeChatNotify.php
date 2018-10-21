<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 16:07
 */

namespace App\Handlers;

use App\Models\TbkOrder;
use App\Transformers\TbkOrderTransformer;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChat\Kernel\Messages\Message;
use Illuminate\Support\Str;

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
     * 通知用户消息(通用消息类型)
     *
     * @param string  $openId
     * @param Message $msg
     */
    public function notifyUser($openId, Message $msg)
    {
        try {
            $this->app->customer_service->message($msg)->to($openId)->send();
        } catch (\Exception $e) {
            \Log::warning("通知用户 $openId 失败: " . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * 通知用户消息(模板消息类型)
     *
     * @param      $openId
     * @param      $templateId
     * @param      $data
     * @param null $url
     *
     * @throws InvalidArgumentException
     */
    protected function notifyByTemplate($openId, $templateId, $data, $url = null)
    {
        $send = [
            'touser' => $openId,
            'template_id' => $templateId,
            'data' => $data,
        ];

        if (!is_null($url)) {
            $send['url'] = $url;
        }

        $result = $this->app->template_message->send($send);

        if (!empty($result['errcode'])) {
            /*
             * 失败
                array:2 [
                    "errcode" => 40037
                    "errmsg" => "invalid template_id hint: [b_pela00233945]"
                ]
            * 成功
                array:3 [
                  "errcode" => 0
                  "errmsg" => "ok"
                  "msgid" => 510964448689995776
                ]
            */

            throw new \Exception($result['errmsg'], $result['errcode']);
        }
    }

    /**
     * 通知用户新的订单
     *
     * @param          $openId
     * @param TbkOrder $tbkOrder
     *
     * @throws InvalidArgumentException
     */
    public function notifyNewOrder($openId, TbkOrder $tbkOrder)
    {
        switch (config('taobaotop.notify_type')) {
            case 'template':
                $data = app(TbkOrderTransformer::class)->newOrderWithTemplate($tbkOrder);
                $this->notifyByTemplate(
                    $openId,
                    config('taobaotop.notify_templates.new_order'),
                    $data,
                    route('wechat.tbkOrder.show')
                );
                break;

            default:
                $this->notifyUser(
                    $openId,
                    app(TbkOrderTransformer::class)->newOrderWithText($tbkOrder)
                );
        }
    }

    /**
     * 通知用户新结算的订单(入账)
     *
     * @param          $openId
     * @param TbkOrder $tbkOrder
     *
     * @throws InvalidArgumentException
     */
    public function notifySettleOrder($openId, TbkOrder $tbkOrder)
    {
        switch (config('taobaotop.notify_type')) {
            case 'template':
                $data = app(TbkOrderTransformer::class)->newRebateWithTemplate($tbkOrder);
                $this->notifyByTemplate($openId, config('taobaotop.notify_templates.new_rebate'), $data);
                break;

            default:
                $this->notifyUser(
                    $openId,
                    app(TbkOrderTransformer::class)->newRebateWithText($tbkOrder)
                );
        }
    }


}
