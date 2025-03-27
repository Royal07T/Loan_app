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
        $formattedAmount = number_format($this->repayment->amount_paid, 2);

        return (new MailMessage)
            ->subject(__("Repayment Confirmation"))
            ->greeting(__("Hello :name,", ['name' => $notifiable->name]))
            ->line(__("You have successfully made a repayment of ₦:amount.", ['amount' => $formattedAmount]))
            ->action(__('View Repayment Details'), route('repayments.index'))
            ->line(__('Thank you for keeping your loan payments up to date.'));
    }

    public function toArray($notifiable)
    {
        return [
            'repayment_id' => $this->repayment->id,
            'amount_paid' => number_format($this->repayment->amount_paid, 2),
            'message' => __("You paid ₦:amount towards your loan.", ['amount' => number_format($this->repayment->amount_paid, 2)])
        ];
    }
}
