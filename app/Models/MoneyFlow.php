<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MoneyFlow extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'balance',
        'type',
        'sub_type',
        'tbk_order_id',
        'comment',
        'channel',
        'account',
    ];

    /**
     * 大类: 收入
     */
    const TYPE_INCOME = 1;

    /**
     * 大类: 支出
     */
    const TYPE_EXPENDITURE = 2;

    /**
     * 子类: 订单结算
     */
    const SUB_TYPE_ORDER_SETTLE = 11;

    /**
     * 子类: 提现
     */
    const SUB_TYPE_WITHDRAW = 21;

    /**
     * 收入流向
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeIncome(Builder $query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    /**
     * 支出流向
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeExpenditure(Builder $query)
    {
        return $query->where('type', self::TYPE_EXPENDITURE);
    }

    /**
     * 提现
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWithdraw(Builder $query)
    {
        return $query->where('sub_type', self::SUB_TYPE_WITHDRAW);
    }

    /**
     * 结算
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOrderSettle(Builder $query)
    {
        return $query->where('sub_type', self::SUB_TYPE_ORDER_SETTLE);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function isIncome()
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isOrderSettle()
    {
        return $this->sub_type === self::SUB_TYPE_ORDER_SETTLE;
    }
}
