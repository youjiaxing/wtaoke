<?php

namespace App\Jobs;

use App\Models\TbkOrder;
use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class NewTbkOrderNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数。
     *
     * @var int
     */
    public $tries = 1;


    /**
     * 任务运行的超时时间。
     *
     * @var int
     */
    public $timeout = 120;

    protected $tbkOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TbkOrder $order)
    {
        $this->tbkOrder = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(\EasyWeChat\OfficialAccount\Application $app)
    {
        $notifyAdmins = [
            "YJX" => "oAcol5xeiVHHzXmHwqxiI_HwBhKU"
        ];

        foreach ($notifyAdmins as $admin => $openId) {
            try {
                $app->customer_service
                    ->message(
                        "同步到新的订单 订单:" . $this->tbkOrder->trade_id .
                        "\n商品:" . $this->tbkOrder->item_title .
                        "\n实际支付:" . $this->tbkOrder->pay_price .
                        "\n订单状态:" . tbkOrderStatusMap($this->tbkOrder->tk_status) .
                        "\n预估佣金:" . $this->tbkOrder->pub_share_pre_fee
                    )
                    ->to($openId)
                    ->send();
            } catch (\Exception $e) {
                \Log::warning("发送通知给管理员失败: " . $e->getMessage());
                continue;
            }

            \Log::info("成功通知管理员 $admin 一条新的订单");
        }
    }
}
