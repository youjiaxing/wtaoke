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
        $totalCount = TbkOrder::where('need_notify', true)->count();
        $this->debug("等待通知的订单共 $totalCount 条");

        if ($totalCount == 0) {
            return;
        }

        // 同步到新订单通知用户
        TbkOrder::where('need_notify', true)
            ->chunk(
                100,
                function ($tbkOrders) use (&$count) {
                    foreach ($tbkOrders as $tbkOrder) {
                        $count++;

                        /* @var TbkOrder $tbkOrder */
                        if ($tbkOrder['user_id'] == 0) {
                            $tbkOrder['need_notify'] = false;
                            $tbkOrder->save();
                            $this->info("忽略未绑定的订单 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                            continue;
                        }

                        try {
                            switch ($tbkOrder->tk_status) {
                                // 3：订单已结算
                                case 3:
                                    $this->notifyNewSettle($tbkOrder);
                                    break;

                                // 12：订单付款
                                case 12:
                                    $this->notifyNewOrder($tbkOrder);
                                    break;

                                // 13：订单失效
                                case 13:
                                    // 忽略, 不做任何处理
                                    break;

                                // 14: 订单成功, 但卖家账户没钱, 无法结算
                                case 14:
                                    $this->notifyFailSettle($tbkOrder);
                                    // 先忽略, 暂时不做任何处理
                                    break;
                            }
                        } catch (\Exception $e) {
                            $this->warn("订单 {$tbkOrder['id']} 通知 {$tbkOrder['user_id']} 失败: " . $e->getMessage());
                        }

                        $tbkOrder['need_notify'] = false;
                        $tbkOrder->save();
                    }
                });
        $this->line("本次共遍历 $count 条订单数据.", $count > 0 ? "comment" : "info");
    }

    /**
     * 同步到新订单
     *
     * @param TbkOrder $tbkOrder
     *
     * @throws \Exception
     */
    protected function notifyNewOrder(TbkOrder $tbkOrder)
    {
        //只通知最近N天内(N取决于微信对于回复消息给用户的限制) - 调试时先关掉
//        if (!$tbkOrder->create_time->lt(Carbon::now()->subDays(3))) {
//            return;
//        }

        $user = $tbkOrder->user;
        $this->comment("通知用户 {$user->name} 有一笔新的订单 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
        app(WeChatNotify::class)->notifyUser(
            $user->weixin_openid,
            app(TbkOrderTransformer::class)->newOrderWithText($tbkOrder)
        );
    }

    protected function notifyNewSettle(TbkOrder $tbkOrder)
    {
        /*
         * 需先结算给用户, 再来通知用户
         * 注意避免骗佣金的情况
         *      -  确认收货N天后才结算给用户 (N=8)
         */

        $user = $tbkOrder->user;

        if ($tbkOrder->is_rebate) {
            // 通知用户钱已经到了
            app(WeChatNotify::class)->notifyUser(
                $user->weixin_openid,
                app(TbkOrderTransformer::class)->newRebateWithText($tbkOrder)
            );
            $this->comment("通知用户 {$user->name} 返利 {$tbkOrder['rebate_fee']} 已到账 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
            return;
        } else {
            // 还未返利, 不用通知
            return;
        }

        $this->comment("通知用户 notifyNewSettle");
    }

    protected function notifyFailSettle(TbkOrder $tbkOrder)
    {
        $this->comment("通知用户 notifyFailSettle");
    }
}
