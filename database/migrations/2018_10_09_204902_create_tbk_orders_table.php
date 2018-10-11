<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTbkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbk_orders', function (Blueprint $table) {
            $table->increments('id');

            $table->boolean('is_rebate')->default(false)->index()->comment("是否已返利给用户")->index();
            $table->decimal('rebate_fee', 12, 4)->nullable()->comment("返利给用户的钱");
            $table->timestamp('rebate_time')->nullable()->comment("返利时间")->index();
            $table->integer('user_id')->default(0)->index()->comment("订单绑定的tbk_user.id");
            $table->timestamps();

            /**
             * 淘宝联盟订单接口响应参数
             * @see http://open.taobao.com/api.htm?docId=38078&docType=2&scopeId=14474
             */
            $table->integer('tk_status')->comment('淘客订单状态，3：订单结算，12：订单付款， 13：订单失效，14：订单成功')->index();
            // 'alipay_total_price' 实际支付金额
            $table->decimal('alipay_total_price', 12, 4)->comment('付款金额: 3.6');
            // 'pub_share_pre_fee' 付款后的预估收入
            $table->decimal('pub_share_pre_fee', 12, 6)->comment('效果预估，付款金额*(佣金比率+补贴比率)*分成比率: 0.03');
            $table->decimal('total_commission_rate', 12, 6)->comment('佣金比率: 0.005');
            $table->decimal('total_commission_fee', 12, 4)->comment('佣金金额: 	0.02');
            $table->string('site_name')->comment('来源媒体名称');
            $table->string('adzone_id')->comment('广告位ID: 47026226');
            $table->string('adzone_name')->comment('广告位名称');
            $table->timestamp('create_time')->comment('淘客订单创建时间: 2018-10-06 14:46:36')->nullable();
            $table->timestamp('earning_time')->comment('淘客订单结算时间: 2018-11-20 10:37:48')->nullable();


            $table->string('trade_parent_id')->comment('淘宝父订单号: 235444578875673437');
            $table->string('trade_id')->comment('淘宝订单号: 235444578875673437')->index();
            $table->string('num_iid')->comment('商品ID: 538517435789');
            $table->string('item_title')->comment('商品标题');
            $table->integer('item_num')->comment('商品数量: 123');
            $table->decimal('price', 12, 4)->comment('单价: 88.00');

            // 'pay_price' 字段有时候会为0, 该字段不可信赖
            $table->decimal('pay_price', 12, 4)->comment('实际支付金额: 85.00');
            $table->string('seller_nick')->comment('卖家昵称');
            $table->string('seller_shop_title')->comment('卖家店铺名称');
            $table->decimal('commission', 12, 4)->comment('推广者获得的收入金额，对应联盟后台报表“预估收入”: 5.00');
            $table->decimal('commission_rate', 12, 6)->comment('推广者获得的分成比率，对应联盟后台报表“分成比率” 20.00');
            $table->string('unid')->comment('推广者unid（已废弃）demo')->nullable();

            $table->string('tk3rd_type')->comment('第三方服务来源，没有第三方服务，取值为“--”')->nullable();
            $table->string('tk3rd_pub_id')->comment('第三方推广者ID')->nullable();
            $table->string('order_type')->comment('订单类型，如天猫，淘宝');
            $table->decimal('income_rate', 12, 6)->comment('收入比率，卖家设置佣金比率+平台补贴比率: 0.008');

            $table->decimal('subsidy_rate', 12, 6)->comment('补贴比率: 0.003');
            $table->string('subsidy_type')->comment('补贴类型，天猫:1，聚划算:2，航旅:3，阿里云:4');
            $table->string('terminal_type')->comment('成交平台，PC:1，无线:2');
            $table->string('auction_category')->comment('类目名称: 办公设备/耗材');
            $table->string('site_id')->comment('来源媒体ID: 12362414');

            $table->decimal('subsidy_fee', 12, 4)->comment('补贴金额');
            $table->string('relation_id')->comment('渠道关系ID')->nullable();
            $table->string('special_id')->comment('会员运营id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbk_orders');
    }
}
