<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\RepaymentNotification;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    protected $signature = 'loan:reminders';
    protected $description = 'Send loan payment due reminders to users.';

    public function handle()
    {
        // Fetch all approved loans with eager loading to reduce queries
        $loans = Loan::where('status', 'approved')->with('user')->get();

        foreach ($loans as $loan) {
            $dueDate = Carbon::parse($loan->created_at)->addMonths($loan->duration);
            $today = Carbon::now();

            if ($today->diffInDays($dueDate) <= 3 && $loan->user) {
                // Ensure user exists and send notification
                $loan->user->notify(new RepaymentNotification($loan));
            }
        }

        $this->info('Payment reminders sent successfully.');
    }
}
