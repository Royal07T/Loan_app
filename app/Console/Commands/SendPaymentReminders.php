<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Loan;
use App\Notifications\PaymentDueReminder;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    protected $signature = 'loan:reminders';
    protected $description = 'Send loan payment due reminders to users.';

    public function handle()
    {
        $loans = Loan::where('status', 'approved')->get();

        foreach ($loans as $loan) {
            $dueDate = $loan->created_at->addMonths($loan->duration);
            $today = Carbon::now();

            if ($today->diffInDays($dueDate) <= 3) { // Send reminder 3 days before due
                $loan->user->notify(new PaymentDueReminder($loan));
            }
        }

        $this->info('Payment reminders sent successfully.');
    }
}
