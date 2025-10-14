<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Check if mail is configured, otherwise use database only
        if (checkMailCreds()) {
            return ['mail', 'database'];
        }
        
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . companyName())
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Welcome to ' . companyName() . '! We\'re excited to have you on board.')
            ->line('You can now browse our solar energy products, build custom systems, and place orders.')
            ->action('Explore Products', url('/products'))
            ->line('If you have any questions, feel free to reach out to our support team.')
            ->line('Thank you for choosing ' . companyName() . '!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Welcome to ' . companyName() . '!',
            'type' => 'welcome',
        ];
    }
}
