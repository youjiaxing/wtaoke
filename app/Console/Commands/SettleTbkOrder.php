<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 21:50
 */

namespace App\Console\Commands;

use App\Console\Command;
use App\Models\TbkOrder;
use App\Models\User;
use Carbon\Carbon;

class SettleTbkOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tbk:settle-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结算淘宝客订单';

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
        $totalCount = TbkOrder::where('tk_status', 3)
            ->where('is_rebate', false)
            ->where('user_id', '<>', 0)
            ->count();
        $this->debug("等待结算的订单共 $totalCount 条");

        if ($totalCount == 0) {
            return;
        }

        // 结算订单
        TbkOrder::where('tk_status', 3)
            ->where('is_rebate', false)
            ->where('user_id', '<>', 0)
            ->chunk(
                100,
                function ($tbkOrders) use (&$count) {
                    foreach ($tbkOrders as $tbkOrder) {
                        $count++;

                        /* @var TbkOrder $tbkOrder */

                        if ($tbkOrder['user_id'] == 0) {
                            $tbkOrder->need_notify = false;
                            $tbkOrder->rebate_fee = 0;
                            $tbkOrder->save();
                            $this->info("忽略未绑定的订单 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                            continue;
                        }

                        $rebate = $this->getRebateFee($tbkOrder);
                        if ($rebate < 0.01) {
                            $tbkOrder->need_notify = false;
                            $tbkOrder->is_rebate = true;
                            $tbkOrder->rebate_fee = 0;
                            $tbkOrder->rebate_time = Carbon::now();
                            $tbkOrder->save();
                            $this->info("忽略无/低佣金 {$rebate} 订单 {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                            continue;
                        }

                        $tbkOrder->need_notify = true;
                        $tbkOrder->is_rebate = true;
                        $tbkOrder->rebate_fee = $rebate;
                        $tbkOrder->rebate_time = Carbon::now();
                        $tbkOrder->save();

                        $newBalance = User::where('id', $tbkOrder['user_id'])->increment('balance', $rebate);
                        $tbkOrder->user->refresh();
                        $this->comment("用户 {$tbkOrder->user->name} 结算一笔订单 , 新入账 $rebate 元, 账户余额为 $newBalance 元. {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                    }
                });
        $this->line("本次共遍历 $count 条订单数据.", $count > 0 ? "comment" : "info");
    }

    protected function getRebateFee(TbkOrder $tbkOrder)
    {
        $rebate = $tbkOrder->total_commission_fee > 0.01 ?
            $tbkOrder->total_commission_fee * config('taobaotop.user_share_rate', 0.5) :
            0;

        return round($rebate, 2, PHP_ROUND_HALF_DOWN);
    }
}