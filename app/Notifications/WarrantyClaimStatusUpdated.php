<?php

namespace App\Notifications;

use App\Models\WarrantyClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarrantyClaimStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $warrantyClaim;

    /**
     * Create a new notification instance.
     */
    public function __construct(WarrantyClaim $warrantyClaim)
    {
        $this->warrantyClaim = $warrantyClaim;
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
        $statusMessages = [
            'SUBMITTED' => 'Your warranty claim has been submitted successfully.',
            'UNDER_REVIEW' => 'Your warranty claim is now under review.',
            'APPROVED' => 'Great news! Your warranty claim has been approved.',
            'REJECTED' => 'Your warranty claim has been rejected.',
            'RESOLVED' => 'Your warranty claim has been resolved.',
        ];

        $message = $statusMessages[$this->warrantyClaim->status] ?? 'Your warranty claim status has been updated.';

        $mailMessage = (new MailMessage)
            ->subject('Warranty Claim Status Update - ' . $this->warrantyClaim->claim_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($message)
            ->line('Claim Number: ' . $this->warrantyClaim->claim_number)
            ->line('Current Status: ' . $this->warrantyClaim->status);

        if ($this->warrantyClaim->admin_notes) {
            $mailMessage->line('Admin Notes: ' . $this->warrantyClaim->admin_notes);
        }

        if ($this->warrantyClaim->estimated_repair_date) {
            $mailMessage->line('Estimated Repair Date: ' . $this->warrantyClaim->estimated_repair_date);
        }

        $mailMessage->action('View Claim Details', url('/warranties'))
            ->line('Thank you for choosing SolaFriq!');

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
            'claim_id' => $this->warrantyClaim->id,
            'claim_number' => $this->warrantyClaim->claim_number,
            'status' => $this->warrantyClaim->status,
            'admin_notes' => $this->warrantyClaim->admin_notes,
            'estimated_repair_date' => $this->warrantyClaim->estimated_repair_date,
        ];
    }
}
