<?php

namespace Statamic\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Statamic\Auth\Passwords\PasswordReset as PasswordResetManager;

class PasswordReset extends Notification
{
    use Queueable;

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
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
            ->subject(__('statamic::messages.reset_password_notification_subject'))
            ->line(__('statamic::messages.reset_password_notification_body'))
            ->action(__('Reset Password'), PasswordResetManager::url($this->token, PasswordResetManager::BROKER_RESETS))
            ->line(__('statamic::messages.reset_password_notification_no_action'));
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
