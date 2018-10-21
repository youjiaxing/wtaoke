<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (\App::environment('prod')) {
            // 每分钟同步前20分钟订单
            $schedule->command('tbk:sync-order')
                ->everyMinute()
                ->after(function () {
                    \Artisan::call('tbk:settle-order');
                    \Artisan::call('tbk:notify-order');
                })
                ->withoutOverlapping(15);
//                ->appendOutputTo(storage_path("logs" . DIRECTORY_SEPARATOR . "timer.log"));

            // 每5分钟同步 前40分钟 ~ 前20分钟 的结算订单
            $start = Carbon::now()->subMinutes(40)->toDateTimeString();
            $end = Carbon::now()->subMinutes(20)->toDateTimeString();
            $schedule->command("tbk:sync-order '{$start}' '{$end}' --settle")
//            $schedule->command("tbk:sync-order '{$start}' '{$end}' --settle")
                ->everyFiveMinutes()
                ->withoutOverlapping(120);


            // 每天 07:00 同步前一天所有订单, 防止丢单
            $start = Carbon::now()->subDays(3)->toDateTimeString();
            $end = Carbon::today()->toDateTimeString();
            $schedule->command("tbk:sync-order '{$start}' '{$end}'")
                ->dailyAt('07:00')
                ->withoutOverlapping(120);

            // 每月 20,21 号定时查询上个月已结算订单
            $start = Carbon::parse("first day of last month")->toDateTimeString();
            $end = Carbon::today()->toDateTimeString();
            $schedule->command("tbk:sync-order '{$start}' '{$end}'")
//            $schedule->command("tbk:sync-order '{$start}' '{$end}' --settle")
                ->cron("0 5 20,21 * *")
                ->withoutOverlapping(120);
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
