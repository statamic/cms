<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\Forms\Connections\EmailConnectionController;
use Statamic\Http\Controllers\CP\Forms\Connections\WebhookConnectionController;
use Statamic\Statamic;

use function Statamic\trans as __;

class CoreConnections
{
    public static function boot()
    {
        FormConnection::register('email')
            ->title(__('Email'))
            ->icon(Statamic::svg('forms/connect/email-notifications'))
            ->description(__('statamic::messages.email_connection_description'))
            ->developer('Statamic')
            ->count(fn (Form $form) => count($form->connections()->get('email', [])))
            ->component('email-connection', fn (Form $form) => [
                'action' => cp_route('forms.connect.email.update', $form->handle()),
                'mailers' => array_keys(config('mail.mailers')),
                'fromAddress' => config('mail.from.address'),
                'templateFolder' => config('statamic.forms.email_view_folder'),
            ])
            ->routes(fn ($router) => $router
                ->patch('/', [EmailConnectionController::class, 'update'])
                ->name('update'));

        FormConnection::register('webhook')
            ->title(__('Webhook'))
            ->icon('globe-arrow')
            ->description(__('statamic::messages.webhook_connection_description'))
            ->developer('Statamic')
            ->count(fn (Form $form) => count($form->connections()->get('webhook', [])))
            ->component('webhook-connection', fn (Form $form) => [
                'action' => cp_route('forms.connect.webhook.update', $form->handle()),
                'examplePayload' => static::exampleWebhookPayload($form),
            ])
            ->routes(fn ($router) => $router
                ->patch('/', [WebhookConnectionController::class, 'update'])
                ->name('update'));
    }

    private static function exampleWebhookPayload(Form $form): array
    {
        $latestSubmission = $form->querySubmissions()->orderBy('date', 'desc')->first();

        return [
            'form' => $form->handle(),
            'submission' => $form->formFields()->fields()
                ->mapWithKeys(function (FormField $field) use ($latestSubmission): array {
                    $value = '…';

                    if ($latestSubmission) {
                        $value = $latestSubmission->data()->get($field->handle());
                    } else {
                        $example = $field->fieldtype()->example();

                        if (is_array($example) && isset($example['value'])) {
                            $value = $example['value'];
                        }
                    }

                    return [$field->handle() => $value];
                })
                ->prepend($latestSubmission?->date() ?? '…', 'date')
                ->prepend($latestSubmission?->id() ?? '…', 'id')
                ->all(),
        ];
    }
}
