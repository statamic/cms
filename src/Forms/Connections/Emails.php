<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\Forms\Connections\EmailConnectionController;
use Statamic\Statamic;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Emails extends Connection
{
    protected static $handle = 'email';

    protected $developer = 'Statamic';

    public function description()
    {
        return __('statamic::messages.email_connection_description');
    }

    public function icon()
    {
        return Statamic::svg('forms/connect/email-notifications');
    }

    public function count(Form $form): ?int
    {
        return count($form->connections()->get('email', []));
    }

    public function render(Form $form): VueComponent
    {
        $fields = static::blueprint()->fields();
        $blank = $fields->preProcess();

        return VueComponent::render('email-connection', [
            'action' => cp_route('forms.connect.email.update', $form->handle()),
            'blueprint' => static::blueprint()->toPublishArray(),
            'rows' => collect($form->connections()->get('email', []))
                ->map(function (array $config) use ($fields) {
                    $row = $fields->addValues($config)->preProcess();

                    return ['values' => $row->values()->all(), 'meta' => $row->meta()->all()];
                })
                ->all(),
            'defaults' => ['values' => $blank->values()->all(), 'meta' => $blank->meta()->all()],
        ]);
    }

    public function routes($router): void
    {
        $router->patch('/', [EmailConnectionController::class, 'update'])->name('update');
    }

    public static function blueprint()
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
}
