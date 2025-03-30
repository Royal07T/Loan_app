<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;
use Carbon\Carbon;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $loan;

    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $dueDate = Carbon::parse($this->loan->due_date)->format('F j, Y');
        $formattedAmount = number_format($this->loan->amount, 2);

        return (new MailMessage)
            ->subject(__("Upcoming Loan Payment Reminder"))
            ->greeting(__("Hello :name,", ['name' => $notifiable->name]))
            ->line(__("Your loan payment of ₦:amount is due on :date.", [
                'amount' => $formattedAmount,
                'date' => $dueDate
            ]))
            ->action(__('View Loan Details'), route('loans.index'))
            ->line(__('Please ensure timely payment to avoid penalties.'));
    }

    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => number_format($this->loan->amount, 2),
            'due_date' => $this->loan->due_date,
            'message' => __("Your loan payment of ₦:amount is due on :date.", [
                'amount' => number_format($this->loan->amount, 2),
                'date' => $this->loan->due_date
            ])
        ];
    }
}
