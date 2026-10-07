<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Forms\Connections\Rules\EmailConnectionAddress;
use Statamic\Forms\Connections\Rules\EmailConnectionView;
use Statamic\Http\Controllers\CP\Forms\EmailConnectionPreviewController;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Email extends Connection
{
    protected $developer = 'Statamic';

    public function description(): ?string
    {
        return __('statamic::messages.email_connection_description');
    }

    public function icon(): ?string
    {
        return Statamic::svg('forms/connect/email-notifications');
    }

    public function smallIcon(): ?string
    {
        return Statamic::svg('forms/connect/email-notifications-small');
    }

    protected function job(Submission $submission, array $row): ?object
    {
        $class = config('statamic.forms.send_email_job');

        return new $class($submission, $submission->site(), $row);
    }

    public function render(Form $form): VueComponent
    {
        $blueprint = static::blueprint($form);
        $fields = $blueprint->fields()->preProcess();

        return VueComponent::render('email-connection', [
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => collect($form->connections()->get('email'))
                ->mapWithKeys(fn (array $config): array => [
                    $config['id'] => $fields->addValues($this->convertLegacyAddresses($config))->preProcess()->meta()->all(),
                ])
                ->all(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
            'previewUrl' => cp_route('forms.connect.email.preview', $form->handle()),
        ]);
    }

    protected function preProcessRow(array $row, Form $form): array
    {
        return static::blueprint($form)->fields()
            ->addValues($this->convertLegacyAddresses($row))
            ->preProcess()
            ->values()
            ->all();
    }

    private function convertLegacyAddresses(array $config): array
    {
        foreach (['to', 'cc', 'bcc', 'reply_to'] as $handle) {
            if (isset($config[$handle]) && is_string($config[$handle])) {
                $config[$handle] = array_map(trim(...), explode(',', $config[$handle]));
            }
        }

        return $config;
    }

    protected function rowRules(Form $form): array
    {
        return [
            'to' => ['required', new EmailConnectionAddress($form)],
            'cc' => [new EmailConnectionAddress($form)],
            'bcc' => [new EmailConnectionAddress($form)],
            'from' => [new EmailConnectionAddress($form)],
            'reply_to' => [new EmailConnectionAddress($form)],
            'html' => [new EmailConnectionView],
            'text' => [new EmailConnectionView],
        ];
    }

    protected function processRow(array $row, Form $form): array
    {
        $values = static::blueprint($form)->fields()
            ->addValues($row)
            ->process()
            ->values()
            ->all();

        return [
            ...$values,
            'markdown' => Arr::get($values, 'markdown') === true ? true : null,
            'attachments' => Arr::get($values, 'attachments') === true ? true : null,
        ];
    }

    public function routes(Router $router): void
    {
        $router->post('preview', EmailConnectionPreviewController::class)->name('preview');
    }

    public static function blueprint(Form $form): \Statamic\Fields\Blueprint
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
                                        'type' => 'form_fields',
                                        'display' => __('Recipient(s)'),
                                        'validate' => ['required'],
                                        'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                        'form' => $form->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'multiple' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'cc',
                                    'field' => [
                                        'type' => 'form_fields',
                                        'display' => __('CC Recipient(s)'),
                                        'form' => $form->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'multiple' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'bcc',
                                    'field' => [
                                        'type' => 'form_fields',
                                        'display' => __('BCC Recipient(s)'),
                                        'form' => $form->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'multiple' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'from',
                                    'field' => [
                                        'type' => 'form_fields',
                                        'display' => __('Sender'),
                                        'instructions' => __('statamic::messages.form_configure_email_from_instructions'),
                                        'placeholder' => config('mail.from.address'),
                                        'form' => $form->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'clearable' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'reply_to',
                                    'field' => [
                                        'type' => 'form_fields',
                                        'display' => __('Reply To'),
                                        'instructions' => __('statamic::messages.form_configure_email_reply_to_instructions'),
                                        'form' => $form->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'multiple' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'subject',
                                    'field' => [
                                        'type' => 'text',
                                        'display' => __('Subject'),
                                    ],
                                ],
                                // TODO: Replace with autocomplete editor once its merged (https://github.com/statamic/cms/pull/15055)
                                [
                                    'handle' => 'body',
                                    'field' => [
                                        'type' => 'textarea',
                                        'display' => __('Message'),
                                        'instructions' => __('statamic::messages.form_configure_email_body_instructions'),
                                    ],
                                ],
                                [
                                    'handle' => 'html',
                                    'field' => [
                                        'type' => 'template',
                                        'display' => __('HTML view'),
                                        'instructions' => __('statamic::messages.form_configure_email_views_instructions'),
                                        'folder' => config('statamic.forms.email_view_folder'),
                                        'width' => 50,
                                        'clearable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'text',
                                    'field' => [
                                        'type' => 'template',
                                        'display' => __('Text view'),
                                        'instructions' => __('statamic::messages.form_configure_email_views_instructions'),
                                        'folder' => config('statamic.forms.email_view_folder'),
                                        'width' => 50,
                                        'clearable' => true,
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
                                        'placeholder' => config('mail.default'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
