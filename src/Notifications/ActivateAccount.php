<?php

namespace Statamic\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Statamic\Auth\Passwords\PasswordReset as PasswordResetManager;

class ActivateAccount extends PasswordReset
{
    public static $subject;
    public static $body;

    public static function subject($subject = null)
    {
        if (is_null($subject)) {
            return static::$subject;
        }

        static::$subject = $subject;
    }

    public static function body($body = null)
    {
        if (is_null($body)) {
            return static::$body;
        }

        static::$body = $body;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(static::$subject ?? __('Activate Your Account'))
            ->line(static::$body ?? __('You are receiving this email because we received a password reset request for your account.'))
            ->action(__('Reset Password'), PasswordResetManager::url($this->token));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
