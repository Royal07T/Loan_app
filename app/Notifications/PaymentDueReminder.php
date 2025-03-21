<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class PaymentDueReminder extends Notification
{
    use Queueable;

    public $loan;

    public function __construct($loan)
    {
        $this->loan = $loan;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Loan Payment Due Reminder')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your loan payment is due soon.')
            ->line('Loan Amount: ₦' . number_format($this->loan->amount, 2))
            ->line('Due Date: ' . $this->loan->created_at->addMonths($this->loan->duration)->toFormattedDateString())
            ->action('Make Payment', url('/repayments'))
            ->line('Please make your payment on time to avoid penalties.');
    }

    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->amount,
            'message' => 'Your loan payment is due soon.',
        ];
    }
}
