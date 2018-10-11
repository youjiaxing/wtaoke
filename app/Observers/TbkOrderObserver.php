<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/9 22:44
 */

namespace App\Observers;

use App\Jobs\NewTbkOrderNotify;
use App\Models\TbkOrder;

class TbkOrderObserver
{
    public function creating(TbkOrder $tbkOrder)
    {
        //TODO 跟踪订单归属
    }

    /**
     * 创建订单
     *
     * @param TbkOrder $tbkOrder
     */
    public function created(TbkOrder $tbkOrder)
    {
        //TODO 若订单有归属, 则通知归属用户

        //TODO 通知管理员微信
        //dispatch(new NewTbkOrderNotify($tbkOrder));

        //TODO 若订单状态为 "3：订单结算", 且订单有归属用户, 则给对应用户增加积分(提取现金) -- 补漏单的情况
    }

    /**
     * 修改订单状态
     *
     * @param TbkOrder $tbkOrder
     */
    public function updated(TbkOrder $tbkOrder)
    {
        //TODO 若订单状态变成 "3：订单结算", 且订单有归属用户, 则给对应用户增加积分(提取现金)
    }
}