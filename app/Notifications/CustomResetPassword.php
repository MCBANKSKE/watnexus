<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    /**
     * Build the branded password reset mail.
     */
    public function toMail($notifiable): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60);

        return (new MailMessage)
            ->subject('Reset your '.config('app.name', 'WatNexus').' password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $this->resetUrl($notifiable))
            ->line('This password reset link will expire in '.$expire.' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Build the reset URL (frontend takes over from here).
     */
    protected function resetUrl($notifiable): string
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }
}
