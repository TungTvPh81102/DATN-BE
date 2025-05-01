<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('withdrawal-request:auto-confirm')->everyMinute();
        $schedule->command('memberships:deactivate-expired')->everyMinute();
        $schedule->command('user:check-activity')->everyMinute();
        $schedule->command('coupons:update-expired-coupons')->everyMinute();
        $schedule->command('posts:publish-scheduled')->everyMinute();
        $schedule->command('live-session:check-overdue')->everyMinute();
        $schedule->command('live-session:notify-upcoming')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
