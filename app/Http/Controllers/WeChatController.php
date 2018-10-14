<?php

namespace App\Http\Controllers;

use App\Models\TbkAdzone;
use App\Models\User;
use App\Transformers\TbkDgMaterialOptionalTransofmer;
use App\Services\TbkApi\TbkApiService;
use Carbon\Carbon;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Text;
use EasyWeChat\Kernel\Messages\Voice;
use Illuminate\Support\Arr;

//use Overtrue\LaravelWeChat\Facade as WeChat;
//use WeChat;

class WeChatController extends Controller
{
    protected $message;

    protected $menuText = <<< EOF
----------------------
0.菜单      显示菜单
1.余额      查询账户当前余额
----------------------
输入淘宝商品名称(完整商品名)自动搜索优惠券
EOF;

    public function serve()
    {
        /* @var \EasyWeChat\OfficialAccount\Application @app */
//        $app = app('wechat.official_account');
//        $app = \EasyWeChat::officialAccount();
//        $app = WeChat::officialAccount();
        $app = \EasyWeChat::officialAccount();

        $app->server->push(function ($message) use ($app) {
            /*
            @see https://www.easywechat.com/docs/master/zh-CN/official-account/server

            请求消息基本属性
            - MsgType       消息类型：event, text....
            - ToUserName    接收方帐号（该公众号 ID）
            - FromUserName  发送方帐号（OpenID, 代表用户的唯一标识）
            - CreateTime    消息创建时间（时间戳）
            - MsgId         消息 ID（64位整型）

            文本：
            - MsgType  text
            - Content  文本消息内容

            图片：
            - MsgType  image
            - MediaId        图片消息媒体id，可以调用多媒体文件下载接口拉取数据。
            - PicUrl   图片链接

            语音：
            - MsgType        voice
            - MediaId        语音消息媒体id，可以调用多媒体文件下载接口拉取数据。
            - Format         语音格式，如 amr，speex 等
            - Recognition * 开通语音识别后才有

            > 请注意，开通语音识别后，用户每次发送语音给公众号时，微信会在推送的语音消息XML数据包中，增加一个 `Recongnition` 字段

            视频：

            - MsgType       video
            - MediaId       视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
            - ThumbMediaId  视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
            小视频：

            - MsgType     shortvideo
            - MediaId     视频消息媒体id，可以调用多媒体文件下载接口拉取数据。
            - ThumbMediaId    视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。

            事件：
            - MsgType     event
            - Event       事件类型 （如：subscribe(订阅)、unsubscribe(取消订阅) ...， CLICK 等）

            # 扫描带参数二维码事件 event = "subscribe"
            - EventKey    事件KEY值，比如：qrscene_123123，qrscene_为前缀，后面为二维码的参数值
            - Ticket      二维码的 ticket，可用来换取二维码图片

            # 上报地理位置事件 event = "LOCATION"
            - Latitude    23.137466   地理位置纬度
            - Longitude   113.352425  地理位置经度
            - Precision   119.385040  地理位置精度

            # 自定义菜单事件 event = "CLICK", 分为 [点击菜单拉取消息时的事件推送], 以及 [点击菜单跳转链接时的事件推送](此时 EventKey 为设置的跳转URL)
            - EventKey    事件KEY值，与自定义菜单接口中KEY值对应，如：CUSTOM_KEY_001, www.qq.com

            地理位置：
            $message->MsgType     location
            $message->Location_X  地理位置纬度
            $message->Location_Y  地理位置经度
            $message->Scale       地图缩放大小
            $message->Label       地理位置信息

            链接：
            $message->MsgType      link
            $message->Title        消息标题
            $message->Description  消息描述
            $message->Url          消息链接

            文件：
            $message->MsgType      file
            $message->Title        文件名
            $message->Description  文件描述，可能为null
            $message->FileKey      文件KEY
            $message->FileMd5      文件MD5值
            $message->FileTotalLen 文件大小，单位字节

            回复的消息可以为 null，此时 SDK 会返回给微信一个 "SUCCESS"，
            你也可以回复一个普通字符串，比如：欢迎关注 overtrue.，
            此时 SDK 会对它进行一个封装，产生一个 EasyWeChat\Kernel\Messages\Text 类型的消息并在最后的 $app->server->serve(); 时生成对应的消息 XML 格式。
            */
            \Log::debug('消息', $message);
            $messageKey = $message['FromUserName'] . "_" . (isset($message['MsgId']) ?? '') . '_' . $message['CreateTime'];
            if (!\Cache::add($messageKey, true, 1)) {
                return;
            }

            $openId = $message['FromUserName'];
            $user = User::where('weixin_openid', $openId)->first();
            if (!$user) {
                // @see https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140839
                $userData = $app->user->get($openId);

                $user = User::create([
                    'name' => $userData['nickname'],
                    'weixin_openid' => $userData['openid'],
                    'avatar' => $userData['headimgurl'],
                ]);

                $tbkAdzones = TbkAdzone::where('user_id', $user->id)->first();
                $tbkAdzoneId = null;
                if (!$tbkAdzones) {
                    TbkAdzone::where('user_id', 0)->limit(1)->update(['user_id' => $user->id]);
                    $tbkAdzones = TbkAdzone::where('user_id', $user->id)->first();

                }

                if ($tbkAdzones) {
                    // 分配专属推广位id
                    $tbkAdzoneId = $tbkAdzones->tbk_adzone_id;
                    $user->tbk_adzone_id = $tbkAdzoneId;
                    $user->tbk_adzone_last_use = Carbon::now();
                    $user->save();

                    //DEBUG
                    if (config('app.debug')) {
                        $app->customer_service->message(new Text("已为您分配专属推广位 $tbkAdzoneId"))
                            ->to($openId)
                            ->send();
                    }

                }
            } else {
                $tbkAdzoneId = $user->tbk_adzone_id;
            }

            // 使用通用推广位id, 无法自动跟踪订单
            $tbkAdzoneId = empty($tbkAdzoneId) ? config('taobaotop.connections.' . config('taobaotop.default') . '.adzoneId') : $tbkAdzoneId;

            app(TbkApiService::class)->setAdzonId($tbkAdzoneId);


            $this->message = $message;

//            \Log::info("收到一条来自 {$message['FromUserName']} 的消息");

//            $user = $app->user->get($message['FromUserName']);
//            \Log::debug("用户信息", $user);
//            \Log::debug("message", $message);
            /*{"subscribe":1,"openid":"oAcol5xeiVHHzXmHwqxiI_HwBhKU","nickname":"嘉兴","sex":1,"language":"zh_CN","city":"泉州","province":"福建","country":"中国","headimgurl":"http://thirdwx.qlogo.cn/mmopen/sVOfnib3Ag7annGE0Qtn6IGxsDMWfaT5eIKrhD2UibpNGpNoPtX2DJkwPQzDa1vdQSKW8cmNMOYIlXFv7ZDs4cTI4miaPm3nne5/132","subscribe_time":1537845361,"remark":"","groupid":0,"tagid_list":[],"subscribe_scene":"ADD_SCENE_QR_CODE","qr_scene":0,"qr_scene_str":""}*/

            switch ($message['MsgType']) {
                case 'event':
                    switch ($message['Event']) {
                        case 'subscribe':
                            return $this->menuText;
                            break;
                    }
                    return $this->menuText;

                    return '收到事件消息 Event: ' . $message['Event'];
                    break;
                case 'text':
                    \Log::info('"text" request arrived: ' . $message['Content']); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

                    $handlers = [
                        [$this, 'keywordReply'],
                        [$this, 'tbkSearchByTitle'],
                        [$this, 'tbkSearchByLink'],
                    ];

                    foreach ($handlers as $handler) {
                        $resp = app()->call($handler);
                        if ($resp !== false) {
                            return $resp;
                        }
                    }
                    return $this->menuText;
                    return "无法识别的口令: " . $message['Content'];
                    break;
                case 'image':
                    return new Image($message['MediaId']);
//                    return "收到图片消息 MediaId: {$message['MediaId']}  PicUrl: {$message['PicUrl']}";
                    break;
                case 'voice':
                    $app->customer_service->message(new Voice($message['MediaId']))
                        ->to($message['FromUserName'])
                        ->send();
                    return '收到语音消息: ' . $message['Recognition'];
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }

//            return "by easywechat！";
        });

        return $app->server->serve();
    }

    /**
     * 根据关键字直接回复
     *
     * @return bool|string
     */
    public function keywordReply()
    {

        switch ($this->message['Content']) {
            case '菜单':
            case 'menu':
            case '0':
                return $this->menuText;
                break;

            case 'link':
                return route('wechat.user', [], true);
                break;

            case '余额':
            case '积分':
            case '钱':
            case '1':
                $openId = $this->message['FromUserName'];
                $user = User::where('weixin_openid', $openId)->first();
                $balance = $user->balance;
                $balanceWithDraw = $user->balance_withdraw;
                return <<< EOF
{$user->name}:
----------------------
账户余额: {$balance} 元
正在提现: {$balanceWithDraw} 元
EOF;

                break;

            case 'exception':
                $obj = new \stdClass();
                return $obj->dsf->dsffdssfda;
                break;

            default:
                return false;
                break;
        }
    }

    /**
     * 解析链接, 并返回处理结果
     *
     * @param \EasyWeChat\OfficialAccount\Application $app
     *
     * @return bool
     */
    public function tbkSearchByLink(\EasyWeChat\OfficialAccount\Application $app)
    {
        return false;

//        $content = $this->message['Content'];
//        if (preg_match('~https?://m\.tb\.cn/\S+~', $content, $matches)) {
//            $url = $matches[0];
//
//        }
    }

    /**
     * 根据查询关键字直接搜索淘宝商品
     *
     * @param \EasyWeChat\OfficialAccount\Application $app
     *
     * @return bool|\EasyWeChat\Kernel\Messages\Message|string
     */
    public function tbkSearchByTitle(\EasyWeChat\OfficialAccount\Application $app)
    {
        $query = trim($this->message['Content']);
        if (mb_strlen($query) <= 6) {
            return false;
        }

        try {
            // 查询是否有对应商品
            $tbkApi = app(TbkApiService::class);
            $materialResp = $tbkApi->dgMaterialOptional($query);
            if (empty($materialResp) || empty($materialResp->result_list)) {
                return "没有找到符合的商品";
            }

            $match = Arr::first($materialResp->result_list->map_data, function ($value) use ($query) {
                return trim($value->title) == $query;
            }, ($materialResp->result_list->map_data)[0]);


            return app(TbkDgMaterialOptionalTransofmer::class)->toWeChatText($match);
        } catch (\Exception $e) {
            return "出现异常: " . $e->getMessage() . "\n" . $e->getTraceAsString();
        }
    }

//    /**
//     * 根据查询关键字直接搜索淘宝商品7
//     *
//     * @param \EasyWeChat\OfficialAccount\Application $app
//     *
//     * @return bool|\EasyWeChat\Kernel\Messages\Message|string
//     */
//    public function tbkSearchByTitle(\EasyWeChat\OfficialAccount\Application $app)
//    {
//        if (mb_strlen(trim($this->message['Content'])) <= 10) {
//            return false;
//        }
//
//        try {
//            $count = 0;
//            // 查询是否有对应商品
//            $tbkApi = app(TbkApiService::class);
//            $couponResp = $tbkApi->dgItemCouponGet(trim($this->message['Content']), "1", "100");
//            if (empty($couponResp->results)) {
//                return "没有符合的商品";
//            }
//
//            // 按销量排序, 并截取前N个
//            $tbk_coupon = $couponResp->results->tbk_coupon;
//            usort($tbk_coupon, function ($a, $b) {
//                return $a->volume < $b->volume;
//            });
//            $tbk_coupon = array_slice($tbk_coupon, 0, 1);
//
//            // 逐个生成淘口令
//            $items = [];
//            foreach ($tbk_coupon as $item) {
//                $tpwdResp = $tbkApi->tpwdCreate($item->coupon_click_url, "<内部优惠>", $item->pict_url);
//                $tkl = $tpwdResp->data->model;
//
//                $item->model = $tkl;
//                $items[] = $item;
//            }
//
//            return app(TbkWeChatTransform::class)->toTklText($item);
//
////            foreach ($items as $item) {
////                $app->customer_service
////                    ->message(app(TbkWeChatTransform::class)->toTklText($item))
////                    ->to($this->message['FromUserName'])
////                    ->send();
////            }
////
////            $count = count($items);
////
////            return "共为您找到 $count 个符合的商品";
//        } catch (\Exception $e) {
//            return "出现异常: ".$e->getMessage()."\n".$e->getTraceAsString();
////            return false;
//        }
//    }
}