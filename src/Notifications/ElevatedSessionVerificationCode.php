<?php

namespace Statamic\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElevatedSessionVerificationCode extends Notification
{
    public function __construct(public string $verificationCode)
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('statamic::messages.elevated_session_verification_code_notification_subject'))
            ->when(true, function ($mailMessage) {
                collect(explode("\n", __('statamic::messages.elevated_session_verification_code_notification_body')))
                    ->filter()
                    ->each(fn ($line) => $mailMessage->line($line));
            })
            ->line("**`{$this->verificationCode}`**");
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
