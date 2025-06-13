<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class KYCStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $status;
    protected $reason;
    protected $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $status, string $reason = null, string $notes = null)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->notes = $notes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject();
        $message = $this->getMessage();

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($message);

        if ($this->status === 'verified') {
            $mailMessage->line('Your KYC verification has been successfully completed.')
                       ->line('You can now access all features of our platform.')
                       ->action('Access Dashboard', route('home'));
        } elseif ($this->status === 'rejected') {
            $mailMessage->line('Unfortunately, your KYC verification could not be completed.')
                       ->line('Reason: ' . ($this->reason ?: 'Not specified'));
            
            if ($this->notes) {
                $mailMessage->line('Additional notes: ' . $this->notes);
            }
            
            $mailMessage->line('Please review the requirements and resubmit your verification.')
                       ->action('Resubmit KYC', route('kyc.index'));
        } elseif ($this->status === 'expired') {
            $mailMessage->line('Your KYC verification has expired.')
                       ->line('Please complete a new verification to continue using our services.')
                       ->action('Complete KYC', route('kyc.index'));
        }

        $mailMessage->line('If you have any questions, please contact our support team.')
                   ->salutation('Best regards, ' . config('app.name') . ' Team');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'kyc_status',
            'status' => $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'message' => $this->getMessage(),
            'user_id' => $notifiable->id,
        ];
    }

    /**
     * Get the notification subject.
     */
    protected function getSubject(): string
    {
        switch ($this->status) {
            case 'verified':
                return 'KYC Verification Approved';
            case 'rejected':
                return 'KYC Verification Rejected';
            case 'expired':
                return 'KYC Verification Expired';
            case 'pending':
                return 'KYC Verification Submitted';
            default:
                return 'KYC Status Update';
        }
    }

    /**
     * Get the notification message.
     */
    protected function getMessage(): string
    {
        switch ($this->status) {
            case 'verified':
                return 'Congratulations! Your KYC verification has been approved.';
            case 'rejected':
                return 'Your KYC verification has been rejected.';
            case 'expired':
                return 'Your KYC verification has expired and needs to be renewed.';
            case 'pending':
                return 'Your KYC verification has been submitted and is under review.';
            default:
                return 'Your KYC verification status has been updated.';
        }
    }
} 