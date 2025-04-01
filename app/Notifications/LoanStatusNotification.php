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

    protected Loan $loan;
    protected string $status;

    public function __construct(Loan $loan, string $status)
    {
        $this->loan = $loan;
        $this->status = $status;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__("Loan Status Update"))
            ->greeting(__("Hello, :name", ['name' => $notifiable->name]))
            ->line(__("Your loan application of ₦:amount has been :status.", [
                'amount' => number_format($this->loan->amount, 2),
                'status' => ucfirst($this->status)
            ]))
            ->action(__('View Loan Details'), route('loans.show', $this->loan->id))
            ->line(__('Thank you for using our loan service.'));
    }

    public function toArray($notifiable): array
    {
        return [
            'loan_id' => $this->loan->id,
            'amount' => number_format($this->loan->amount, 2),
            'status' => ucfirst($this->status),
            'message' => __("Your loan of ₦:amount is now :status.", [
                'amount' => number_format($this->loan->amount, 2),
                'status' => ucfirst($this->status)
            ])
        ];
    }
}
// Compare this snippet from app/Providers/AppServiceProvider.php: