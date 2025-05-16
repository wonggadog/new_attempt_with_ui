<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These cron jobs are run in the background and do not require user interaction.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $now = \Carbon\Carbon::now();
            $reminderDate = $now->copy()->addDays(3)->format('Y-m-d');
            $dueDocs = \App\Models\CommunicationForm::where('due_date', $reminderDate)
                ->whereNull('deleted_at')
                ->get();
            foreach ($dueDocs as $doc) {
                $user = \App\Models\User::where('name', $doc->to)->first();
                if ($user && $user->email) {
                    \Mail::to($user->email)->send(new \App\Mail\DueDateReminder($doc));
                }
            }
        })->dailyAt('08:00');
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
} 