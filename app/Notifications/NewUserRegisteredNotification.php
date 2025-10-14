<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
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
            ->subject('New User Registration - ' . $this->newUser->name)
            ->greeting('Hello Admin,')
            ->line('A new user has registered on ' . companyName() . '.')
            ->line('**User Details:**')
            ->line('Name: ' . $this->newUser->name)
            ->line('Email: ' . $this->newUser->email)
            ->line('Phone: ' . ($this->newUser->phone_number ?? 'Not provided'))
            ->line('Registered: ' . $this->newUser->created_at->format('M d, Y h:i A'))
            ->action('View User', url('/admin/users/' . $this->newUser->id))
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->newUser->id,
            'user_name' => $this->newUser->name,
            'user_email' => $this->newUser->email,
            'message' => 'New user registered: ' . $this->newUser->name,
            'type' => 'new_user',
        ];
    }
}
