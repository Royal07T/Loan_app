<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Models\User;
use App\Notifications\PaymentReminderNotification;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    protected $signature = 'loan:reminders';
    protected $description = 'Send loan payment due reminders to users.';

    public function handle()
    {
        // Fetch approved loans with a due date within the next 3 days
        $loans = Loan::where('status', 'approved')
            ->whereDate('due_date', '<=', Carbon::now()->addDays(3))
            ->with('user')
            ->get();

        foreach ($loans as $loan) {
            if ($loan->user) {
                // Send reminder notification
                $loan->user->notify(new PaymentReminderNotification($loan));
            }
        }

        $this->info('Payment reminders sent successfully.');
    }
}
