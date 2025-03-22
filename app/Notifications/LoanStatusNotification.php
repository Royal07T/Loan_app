<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;

class LoanStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $loan;
    private $status;

    public function __construct(Loan $loan, $status)
    {
        $this->loan = $loan;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // Notify via email & database
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Loan Status Update")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your loan application of ₦{$this->loan->amount} has been {$this->status}.")
            ->action('View Loan Details', url('/loans'))
            ->line('Thank you for using our loan service.');
    }

    public function toArray($notifiable)
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->amount,
            'status' => $this->status,
            'message' => "Your loan of ₦{$this->loan->amount} is now {$this->status}."
        ];
    }
}
