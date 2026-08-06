<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Forms\SendEmails;
use Statamic\Http\Controllers\CP\Forms\Connections\EmailConnectionController;
use Statamic\Statamic;
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

    public function count(Form $form): ?int
    {
        return count($form->connections()->get('email', []));
    }

    public function finalized(Submission $submission): object|array
    {
        return new SendEmails($submission, $submission->site());
    }

    public function render(Form $form): VueComponent
    {
        $fields = static::blueprint($form)->fields()->preProcess();

        return VueComponent::render('email-connection', [
            'action' => cp_route('forms.connect.email.update', $form->handle()),
            'blueprint' => static::blueprint($form)->toPublishArray(),
            'emails' => collect($form->connections()->get('email'))
                ->mapWithKeys(function (array $config) use ($fields): array {
                    // Convert legacy address strings to arrays.
                    foreach (['to', 'cc', 'bcc', 'reply_to'] as $handle) {
                        if (isset($config[$handle]) && is_string($config[$handle])) {
                            $config[$handle] = array_map(trim(...), explode(',', $config[$handle]));
                        }
                    }

                    $fields = $fields->addValues($config)->preProcess();

                    return [$config['id'] => [
                        'values' => $fields->values()->all(),
                        'meta' => $fields->meta()->all(),
                    ]];
                })
                ->all(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
        ]);
    }

    public function routes(Router $router): void
    {
        $router->patch('/', [EmailConnectionController::class, 'update'])->name('update');
    }

    public static function blueprint(Form $form): \Statamic\Fields\Blueprint
    {
        $addressOptions = $form->formFields()->fields()
            ->reject(fn ($field) => array_intersect($field->fieldtype()->categories(), ['information', 'structure']))
            ->mapWithKeys(fn ($field) => ['field:'.$field->handle() => $field->display()])
            ->all();

        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'to',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Recipient(s)'),
                                        'validate' => ['required'],
                                        'instructions' => __('statamic::messages.form_configure_email_to_instructions'),
                                        'options' => $addressOptions,
                                        'multiple' => true,
                                        'taggable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'cc',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('CC Recipient(s)'),
                                        'options' => $addressOptions,
                                        'multiple' => true,
                                        'taggable' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'bcc',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('BCC Recipient(s)'),
                                        'options' => $addressOptions,
                                        'multiple' => true,
                                        'taggable' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'from',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Sender'),
                                        'instructions' => __('statamic::messages.form_configure_email_from_instructions'),
                                        'options' => $addressOptions,
                                        'placeholder' => config('mail.from.address'),
                                        'taggable' => true,
                                        'clearable' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'reply_to',
                                    'field' => [
                                        'type' => 'select',
                                        'display' => __('Reply To'),
                                        'instructions' => __('statamic::messages.form_configure_email_reply_to_instructions'),
                                        'options' => $addressOptions,
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
                                // TODO: Add message field w/ autocomplete editor once its merged (https://github.com/statamic/cms/pull/15055)
                                [
                                    'handle' => 'html',
                                    'field' => [
                                        'type' => 'template',
                                        'display' => __('HTML view'),
                                        'instructions' => __('statamic::messages.form_configure_email_views_instructions'),
                                        'folder' => config('statamic.forms.email_view_folder'),
                                        'width' => 50,
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
