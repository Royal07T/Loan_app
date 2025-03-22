<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Repayment;

class RepaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $repayment;

    public function __construct(Repayment $repayment)
    {
        $this->repayment = $repayment;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Repayment Confirmation")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have successfully made a repayment of ₦{$this->repayment->amount_paid}.")
            ->action('View Repayment Details', url('/repayments'))
            ->line('Thank you for keeping your loan payments up to date.');
    }

    public function toArray($notifiable)
    {
        return [
            'repayment_id' => $this->repayment->id,
            'amount_paid' => $this->repayment->amount_paid,
            'message' => "You paid ₦{$this->repayment->amount_paid} towards your loan."
        ];
    }
}
