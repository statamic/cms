<?php

namespace Statamic\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecoveryCodeUsed extends Notification
{
    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('statamic-two-factor::messages.recovery_code_used_subject'))
            ->greeting(__('statamic-two-factor::messages.recovery_code_used_greeting', ['name' => $notifiable->name()]))
            ->line(__('statamic-two-factor::messages.recovery_code_used_body'))
            ->line(__('statamic-two-factor::messages.recovery_code_used_body_2'))
            ->action(__('statamic-two-factor::messages.recovery_code_used_action'), cp_route('account'))
            ->line(__('statamic-two-factor::messages.recovery_code_used_body_3'));
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
