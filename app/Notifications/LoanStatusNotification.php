<?php


namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LoanStatusNotification extends Notification
{
    use Queueable;

    public $loan;
    public $status;

    public function __construct($loan, $status)
    {
        $this->loan = $loan;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Send Email + Save in DB
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Loan Status Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your loan application has been ' . strtoupper($this->status) . '.')
            ->line('Loan Amount: ₦' . number_format($this->loan->amount, 2))
            ->line('Status: ' . strtoupper($this->status))
            ->action('View Loan', url('/loans'))
            ->line('Thank you for using our loan service!');
    }

    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->amount,
            'status' => $this->status,
            'message' => 'Your loan application has been ' . strtoupper($this->status),
        ];
    }
}
