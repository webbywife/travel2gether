<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/** Branded reset email — a link only, nothing else from the account. */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $minutes = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Reset your Travel2gether password')
            ->greeting('Hi ' . ($notifiable->name ?: 'there') . ',')
            ->line('Someone (hopefully you) asked to reset the password for your Travel2gether account.')
            ->action('Choose a new password', $url)
            ->line("This link works once and expires in {$minutes} minutes.")
            ->line("If you didn't ask for this, you can ignore this email — your password stays the same.")
            ->salutation('— Travel2gether');
    }
}
