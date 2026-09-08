<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an existing account when someone tries to register again with its
 * email address — lets the real owner know without the registration form ever
 * confirming (to the attacker) that the address exists.
 */
class RegistrationAttempted extends Notification
{
    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Someone tried to sign up with your email')
            ->greeting('Hi ' . ($notifiable->name ?? 'there') . ',')
            ->line('We received a sign-up attempt for Travel2gether using this email address, but you already have an account.')
            ->line('If this was you, just log in — or reset your password if you have forgotten it.')
            ->action('Go to log in', route('login'))
            ->line('If it was not you, no action is needed. Your account was not changed.');
    }
}
