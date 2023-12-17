<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->executePriceUpdate($schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Execute price update command.
     */
    private function executePriceUpdate(Schedule $schedule): void
    {
        $minutesPerUpdate = config('metalshop.rss.update_per_minutes');

        $schedule->command('store:price:update')->cron("*/{$minutesPerUpdate} * * * *");
    }
}
