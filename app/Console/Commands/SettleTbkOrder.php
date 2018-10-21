<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/11 21:50
 */

namespace App\Console\Commands;

use App\Console\Command;
use App\Handlers\TbkRebateHandler;
use App\Models\MoneyFlow;
use App\Models\TbkOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

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
    public function handle(TbkRebateHandler $tbkRebateHandler)
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
                function ($tbkOrders) use (&$count, $tbkRebateHandler) {
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

                        $rebate = $tbkRebateHandler->getRebate($tbkOrder, false);
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

                        $result = User::where('id', $tbkOrder['user_id'])->increment('balance', $rebate);
                        $user = $tbkOrder->user->refresh();
                        $moneyFlow = $tbkOrder->user->moneyFlows()->create([
                            'amount' => $rebate,
                            'balance' => $user->balance,
                            'type' => MoneyFlow::TYPE_INCOME,
                            'sub_type' => MoneyFlow::SUB_TYPE_ORDER_SETTLE,
                            'tbk_order_id' => $tbkOrder->id,
                            'comment' => "订单: {$tbkOrder->trade_id}, 商品: " . Str::limit($tbkOrder->item_title),
                        ]);
                        $this->comment("用户 {$tbkOrder->user->name} 结算一笔订单, 操作影响结果: {$result} , 新入账 $rebate 元, 账户余额为 {$tbkOrder->user->balance} 元. {$tbkOrder['trade_id']}  {$tbkOrder['item_title']}");
                    }
                });
        $this->line("本次共遍历 $count 条订单数据.", $count > 0 ? "comment" : "info");
    }
}