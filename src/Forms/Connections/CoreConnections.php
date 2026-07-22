<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Blueprint;
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
            ->component('email-connection', function (Form $form) {
                $fields = static::emailBlueprint()->fields();
                $blank = $fields->preProcess();

                return [
                    'action' => cp_route('forms.connect.email.update', $form->handle()),
                    'blueprint' => static::emailBlueprint()->toPublishArray(),
                    'rows' => collect($form->connections()->get('email', []))
                        ->map(function (array $config) use ($fields) {
                            $row = $fields->addValues($config)->preProcess();

                            return ['values' => $row->values()->all(), 'meta' => $row->meta()->all()];
                        })
                        ->all(),
                    'defaults' => ['values' => $blank->values()->all(), 'meta' => $blank->meta()->all()],
                ];
            })
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

    public static function emailBlueprint()
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'to',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Recipient(s)'),
                                        'validate' => ['required'],
                                        'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                    ],
                                ],
                                [
                                    'handle' => 'cc',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('CC Recipient(s)'),
                                        'instructions' => __('statamic::messages.form_configure_email_cc_instructions'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'bcc',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('BCC Recipient(s)'),
                                        'instructions' => __('statamic::messages.form_configure_email_bcc_instructions'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'from',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Sender'),
                                        'instructions' => __('statamic::messages.form_configure_email_from_instructions').' ('.config('mail.from.address').').',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'reply_to',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Reply To'),
                                        'instructions' => __('statamic::messages.form_configure_email_reply_to_instructions'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'subject',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Subject'),
                                        'instructions' => __('statamic::messages.form_configure_email_subject_instructions'),
                                    ],
                                ],
                                [
                                    'handle' => 'html',
                                    'field' => [
                                        'type' => 'template',
                                        'display' => __('HTML view'),
                                        'instructions' => __('statamic::messages.form_configure_email_html_instructions'),
                                        'folder' => config('statamic.forms.email_view_folder'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'text',
                                    'field' => [
                                        'type' => 'template',
                                        'display' => __('Text view'),
                                        'instructions' => __('statamic::messages.form_configure_email_text_instructions'),
                                        'folder' => config('statamic.forms.email_view_folder'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'markdown',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('Markdown'),
                                        'instructions' => __('statamic::messages.form_configure_email_markdown_instructions'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'attachments',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('Attachments'),
                                        'instructions' => __('statamic::messages.form_configure_email_attachments_instructions'),
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'mailer',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Mailer'),
                                        'instructions' => __('statamic::messages.form_configure_mailer_instructions'),
                                        'options' => array_keys(config('mail.mailers')),
                                        'clearable' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private static function exampleWebhookPayload(Form $form): array
    {
        $latestSubmission = null;

        if (User::current()->can('viewSubmissions', $form)) {
            $latestSubmission = $form->querySubmissions()->orderBy('date', 'desc')->first();
        }

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
