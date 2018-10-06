<?php

namespace App\Providers;

use App\Services\TbkApi\TbkApiService;
use Carbon\Carbon;
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
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TbkApiService::class, function ($app) {
            $topClient = \TopClient::connection();
            $topClient->format = 'json';
            $client = new TbkApiService($topClient);
            $client->setAdzonId(config('taobaotop.connections.'.config('taobaotop.default').'.adzoneId'));
            return $client;
        });
    }
}
