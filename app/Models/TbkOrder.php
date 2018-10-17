<?php

namespace App\Models;

use App\Handlers\TbkRebateHandler;
use Illuminate\Database\Eloquent\Model;

class TbkOrder extends Model
{
    protected $fillable = [
        'trade_parent_id',
        'trade_id',
        'num_iid',
        'item_title',
        'item_num',
        'price',
        'pay_price',
        'seller_nick',
        'seller_shop_title',
        'commission',
        'commission_rate',
        'unid',
        'create_time',
        'earning_time',
        'tk_status',
        'tk3rd_type',
        'tk3rd_pub_id',
        'order_type',
        'income_rate',
        'pub_share_pre_fee',
        'subsidy_type',
        'terminal_type',
        'auction_category',
        'site_id',
        'site_name',
        'adzone_id',
        'adzone_name',
        'alipay_total_price',
        'total_commission_rate',
        'total_commission_fee',
        'subsidy_fee',
        'relation_id',
        'special_id',
    ];

    protected $dates = ['create_time', 'earning_time', 'rebate_time'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 已结算
     * @param $query
     *
     * @return mixed
     */
    public function scopeSettled($query)
    {
        return $query->where('tk_status', 3);
    }

    /**
     * 已下单
     * @param $query
     *
     * @return mixed
     */
    public function scopeOrdered($query)
    {
        return $query->where('tk_status', 12);
    }

    /**
     * 已取消
     * @param $query
     *
     * @return mixed
     */
    public function scopeCanceled($query)
    {
        return $query->where('tk_status', 13);
    }

    /**
     * 已返利
     * @param $query
     *
     * @return mixed
     */
    public function scopeRebated($query)
    {
        return $query->where('is_rebate', true);
    }

    public function calcRebate($serviceFeeRate = null, $userShareRate = null)
    {
        return app(TbkRebateHandler::class)
            ->getRebate($this, $this->tk_status == 3 ? false : true, $serviceFeeRate, $userShareRate);
    }
}
