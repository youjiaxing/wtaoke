<?php

namespace App\Console;

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
//            $schedule->command('tbk:sync-order --notify')
            $schedule->command('tbk:sync-order')
                ->everyMinute()
                ->after(function () {
                    \Artisan::call('tbk:settle-order');
                    \Artisan::call('tbk:notify-order');
                })
                ->withoutOverlapping(15);
//                ->appendOutputTo(storage_path("logs" . DIRECTORY_SEPARATOR . "timer.log"));
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
