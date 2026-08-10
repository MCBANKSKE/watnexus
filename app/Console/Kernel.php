<?php

namespace App\Console;

use App\Jobs\UpdateCurrencyExchangeRates;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Update currency exchange rates daily at 2 AM
        $schedule->call(function () {
            UpdateCurrencyExchangeRates::dispatch();
        })->dailyAt('02:00')->name('update-currency-exchange-rates');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
