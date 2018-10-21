<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('avatar')->nullable()->comment("头像");
            $table->string('alipay_account')->nullable()->comment("绑定的支付宝账号, 体现用");
            $table->string('tbk_adzone_id')->nullable()->unique()->comment("分配的推广位id");
            $table->timestamp('tbk_adzone_last_use')->nullable()->comment("推广位id最后一次使用时间, 在推广位资源紧缺时可以回收");

            $table->string('weixin_openid')->nullable()->index()->comment("微信openid");
            $table->string('weixin_unionid')->nullable()->index()->comment("微信unionid");
            $table->boolean('weixin_subscribe')->nullable()->comment("是否关注");

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
