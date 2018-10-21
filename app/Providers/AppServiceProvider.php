<?php

namespace App\Providers;

use App\Handlers\TbkRebateHandler;
use App\Models\TbkOrder;
use App\Observers\TbkOrderObserver;
use App\Services\TbkApi\TbkApiService;
use App\Services\TbkThirdApi\Api\KoussApi;
use App\Services\TbkThirdApi\Api\MiaoYouQuanApi;
use App\Services\TbkThirdApi\Manager;
use App\Transformers\TbkOrderTransformer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));
        TbkOrder::observe(TbkOrderObserver::class);
        Schema::defaultStringLength(191);
//        class_alias(\Overtrue\LaravelWeChat\Facade::class, "WeChat");
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 官方淘宝客服务调用
        $this->app->singleton(TbkApiService::class, function ($app) {
            $topClient = \TopClient::connection();
            $topClient->format = 'json';
            $client = new TbkApiService($topClient);
            $client->setAdzonId(config('taobaotop.connections.' . config('taobaotop.default') . '.adzoneId'));
            return $client;
        });

        // 淘宝客第三方API调用服务注册
        $this->app->singleton(Manager::class, function ($app) {
            $manager = new Manager();
            $manager->pushApi($app->get(KoussApi::class));

            $manager->pushApi($app->get(MiaoYouQuanApi::class));
            return $manager;
        });

        // 第三方API - KoussApi
        $this->app->singleton(KoussApi::class, function () {
            return new KoussApi(
                config('taobaotop.third.kouss.session'),
                config('taobaotop.third.kouss.debug')
            );
        });

        // 第三方API - MiaoYouQuan
        $this->app->singleton(MiaoYouQuanApi::class, function () {
            return new MiaoYouQuanApi(
                config('taobaotop.third.miao_you_quan.app_key'),
                config('taobaotop.third.miao_you_quan.tb_name'),
                config('taobaotop.third.miao_you_quan.vip_lv')
            );
        });

        $this->app->alias(Manager::class, "tbk.third.manager");

        $this->app->singleton(TbkRebateHandler::class);
        $this->app->singleton(TbkOrderTransformer::class);
    }
}
