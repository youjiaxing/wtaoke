<?php

namespace App\Models;

use App\Models\WithdrawHistory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'weixin_openid',
        'weixin_unionid',
        'alipay_account',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'tbk_adzone_last_use'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tbkOrders()
    {
        return $this->hasMany(TbkOrder::class, 'user_id', 'id');
    }

    public function moneyFlows()
    {
        return $this->hasMany(MoneyFlow::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawHistories()
    {
        return $this->hasMany(WithdrawHistory::class, 'user_id', 'id');
    }
}
