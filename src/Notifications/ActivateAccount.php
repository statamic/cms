<?php

namespace Statamic\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Statamic\Auth\Passwords\PasswordReset as PasswordResetManager;

class ActivateAccount extends PasswordReset
{
    public static $subject;
    public static $greeting;
    public static $body;

    public static function subject($subject = null)
    {
        if (is_null($subject)) {
            return static::$subject;
        }

        static::$subject = $subject;
    }

    public static function greeting($greeting = null)
    {
        if (is_null($greeting)) {
            return static::$greeting;
        }

        static::$greeting = $greeting;
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
            ->subject(static::$subject ?? __('statamic::messages.activate_account_notification_subject'))
            ->greeting(static::$greeting)
            ->line(static::$body ?? __('statamic::messages.activate_account_notification_body'))
            ->action(__('Activate Account'), PasswordResetManager::url($this->token, PasswordResetManager::BROKER_ACTIVATIONS));
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
