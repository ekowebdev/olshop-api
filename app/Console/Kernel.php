<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('send:birthdaywish')->daily();
        $schedule->command('update:order')->hourly();
        $schedule->command('delete:searchlogs')->daily();
        $schedule->command('delete:users')->daily();
        $schedule->command('delete:carts')->daily();
        $schedule->command('update:product-aggregates')->dailyAt('01:00');
        // $schedule->command('update:product-aggregates')->monthlyOn(1, '01:00');
        // $schedule->command('update:product-aggregates')->weeklyOn(1, '01:00');
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
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'Asia/Jakarta';
    }
}
