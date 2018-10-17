<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoneyFlowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_flows', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index();
            $table->decimal('amount')->comment('收入/提取金额');
            $table->decimal('balance')->comment('操作后的用户余额');
            $table->integer('type')->comment('操作类型 1:收入 2:支出 3:其他?')->index();
            // 后期可能新增 12:平台补偿  22:维权订单扣除
            $table->integer('sub_type')->comment('子类型 11:订单结算 21:提现');
            $table->string('tbk_order_id')->nullable()->comment('结算的订单id');
            $table->string('comment')->nullable();
            $table->string('channel')->comment('提现渠道')->nullable();
            // 同时在 comment 字段中写入"提现渠道: 提现账号"
            $table->string('account')->comment('提现账号')->nullable();
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('money_flow');
    }
}
