<?php
/**
 *
 * @author : 尤嘉兴
 * @version: 2018/10/14 23:19
 */

namespace App\Handlers;

use App\Models\TbkOrder;

class TbkRebateHandler
{
    /**
     *
     *
     * @param TbkOrder    $tbkOrder
     * @param boolean     $pre
     *      - true 计算预付佣金
     *      - false 计算实际佣金
     * @param null|double $serviceFeeRate
     * @param null|double $userShareRate
     *
     * @return float
     */
    public function getRebate(TbkOrder $tbkOrder, $pre, $serviceFeeRate = null, $userShareRate = null)
    {
        $fee = $pre ? $tbkOrder->pub_share_pre_fee : $tbkOrder->total_commission_fee;
        return $this->calcRebate($fee, $serviceFeeRate, $userShareRate);
    }

    /**
     * @param float $fee 原始返利
     * @param null  $serviceFeeRate
     * @param null  $userShareRate
     *
     * @return float
     */
    public function calcRebate($fee, $serviceFeeRate = null, $userShareRate = null)
    {
        $serviceFeeRate = is_null($serviceFeeRate) ? config('taobaotop.service_fee_rate', 0.1) : $serviceFeeRate;
        $userShareRate = is_null($userShareRate) ? config('taobaotop.user_share_rate', 1) : $userShareRate;

        $rebate = $fee > 0 ?
            max(
                0.01,
                $fee * (1 - $serviceFeeRate) * $userShareRate
            ) :
            0;
        return round($rebate, 2, PHP_ROUND_HALF_DOWN);
    }
}