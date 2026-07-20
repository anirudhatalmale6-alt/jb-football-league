<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Password Reset Request')
            ->greeting('Hello,')
            ->line('We received a request to reset your password for your JBFA account.')
            ->line('Please click the button below to reset your password:')
            ->action('Reset Password', $url)
            ->line("This link will expire in {$expire} minutes and can only be used once.")
            ->line('If you did not request this, you may ignore this email.')
            ->salutation('Thank you.');
    }
}
