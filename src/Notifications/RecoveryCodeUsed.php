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
            ->subject(__('statamic::messages.two_factor_recovery_code_used_notification_subject'))
            ->when(true, function ($mailMessage) {
                collect(explode("\n", __('statamic::messages.two_factor_recovery_code_used_notification_body')))
                    ->filter()
                    ->each(fn ($line) => $mailMessage->line($line));
            })
            ->action(__('View Profile'), cp_route('account'));
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
