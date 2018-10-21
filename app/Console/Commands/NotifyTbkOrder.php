<?php

namespace App\Console\Commands;

use App\Handlers\WeChatNotify;
use App\Models\TbkOrder;
use App\Transformers\TbkOrderTransformer;
use App\Console\Command;
use Carbon\Carbon;

class NotifyTbkOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbk:notify-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '订单状态有变化时, 通知绑定的对应用户';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $count = 0;
        $failCount = 0;
        $totalCount = TbkOrder::where('need_notify', true)->count();
        $this->debug("等待通知的订单共 $totalCount 条");

        if ($totalCount == 0) {
            return;
        }

        // 同步到新订单通知用户
        TbkOrder::where('need_notify', true)
            ->chunk(
                100,
                function ($tbkOrders) use (&$count, &$failCount) {
                    foreach ($tbkOrders as $tbkOrder) {
                        $count++;

                        /* @var TbkOrder $tbkOrder */
                        if ($tbkOrder['user_id'] == 0) {
                            $tbkOrder['need_notify'] = false;
                            $tbkOrder->save();
                            $this->info("忽略未绑定的订单 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                            continue;
                        }

                        $result = false;
                        switch ($tbkOrder->tk_status) {
                            // 3：订单已结算
                            case 3:
                                $result = $this->notifyNewSettle($tbkOrder);
                                break;

                            // 12：订单付款
                            case 12:
                                $result = $this->notifyNewOrder($tbkOrder);
                                break;

                            // 13：订单失效
                            case 13:
                                $result = true;
                                // 忽略, 不做任何处理
                                break;

                            // 14: 订单成功, 但卖家账户没钱, 无法结算
                            case 14:
                                $result = $this->notifyFailSettle($tbkOrder);
                                // 先忽略, 暂时不做任何处理
                                break;

                            default:
                                $result = true;
                                $this->error("未知的订单状态: {$tbkOrder->tk_status}, " . json($tbkOrder));
                        }

                        if ($result) {
                            $tbkOrder['need_notify'] = false;
                            $tbkOrder->save();
                        } else {
                            $failCount++;
                        }
                    }
                }
            );

        if ($failCount > 0) {
            $this->warn("本次共遍历 $count 条订单数据, 其中失败 $failCount 条.");
        } else {
            $this->line("本次共遍历 $count 条订单数据.", $count > 0 ? "comment" : "info");
        }
    }

    /**
     * 通知新订单
     *
     * @param TbkOrder $tbkOrder
     *
     * @return bool
     */
    protected function notifyNewOrder(TbkOrder $tbkOrder)
    {
        $user = $tbkOrder->user;

        $cacheKey = "notify_new_order_{$tbkOrder->id}";
        $notifyCount = \Cache::get($cacheKey, 0);
        if ($notifyCount > 1) {
            $this->warn("通知用户 {$user->name} 有一笔新的订单 {$tbkOrder->trade_id} 失败次数达到上限, 稍后将重试");
            return false;
        }


        try {
            app(WeChatNotify::class)->notifyNewOrder($user->weixin_openid, $tbkOrder);
        } catch (\Exception $e) {
            if ($e->getCode() != 43004) {
                $this->warn("通知用户 {$user->name} 有一笔新的订单时错误:'errorcode:{$e->getCode()} {$e->getMessage()}', 订单号:{$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                \Cache::set($cacheKey, $notifyCount + 1, 10);
                return false;
            }
        }

        $this->comment("通知用户 {$user->name} 有一笔新的订单成功. {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
        \Cache::delete($cacheKey);
        return true;
    }

    /**
     * @param TbkOrder $tbkOrder
     *
     * @return bool
     */
    protected function notifyNewSettle(TbkOrder $tbkOrder)
    {
        /*
         * 需先结算给用户, 再来通知用户
         * 注意避免骗佣金的情况
         *      -  确认收货N天后才结算给用户 (N=8)
         */
        $user = $tbkOrder->user;

        $cacheKey = "notify_new_settle_{$tbkOrder->id}";
        $notifyCount = \Cache::get($cacheKey, 0);
        if ($notifyCount > 1) {
            $this->warn("通知用户 {$user->name} 有一笔新的返利到账 {$tbkOrder->trade_id} 失败次数达到上限, 稍后将重试");
            return false;
        }


        if ($tbkOrder->is_rebate) {
            // 通知用户钱已经到了
            try {
                app(WeChatNotify::class)->notifySettleOrder($user->weixin_openid, $tbkOrder);
            } catch (\Exception $e) {
                if ($e->getCode() != 43004) {


                    $this->warn("通知用户 {$user->name} 返利 {$tbkOrder['rebate_fee']} 已到账时错误: 'errorcode:{$e->getCode()} {$e->getMessage()}', 订单号:{$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                    \Cache::set($cacheKey, $notifyCount + 1, 10);
                    return false;
                }
            }

            $this->comment("通知用户 {$user->name} 返利 {$tbkOrder['rebate_fee']} 已到账 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
        } else {
            // 还未返利, 不用通知
        }

        \Cache::delete($cacheKey);
        return true;
    }

    protected function notifyFailSettle(TbkOrder $tbkOrder)
    {
        $this->comment("通知用户 notifyFailSettle");
        return true;
    }
}
