<?php

namespace Statamic\Forms\Connectors;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Forms\Connectors\Rules\EmailConnectionAddress;
use Statamic\Forms\Connectors\Rules\EmailConnectionTemplateSyntax;
use Statamic\Forms\Connectors\Rules\EmailConnectionView;
use Statamic\Http\Controllers\CP\Forms\EmailConnectionPreviewController;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Email extends Connector
{
    protected $developer = 'Statamic';

    public function description(): ?string
    {
        return __('statamic::messages.email_connector_description');
    }

    public function icon(): ?string
    {
        return Statamic::svg('forms/connect/email-notifications');
    }

    public function smallIcon(): ?string
    {
        return Statamic::svg('forms/connect/email-notifications-small');
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        $class = config('statamic.forms.send_email_job');

        return new $class($submission, $submission->site(), static::stringifyAddresses($connection));
    }

    /** @internal */
    public static function stringifyAddresses(array $connection): array
    {
        foreach (['to', 'cc', 'bcc', 'reply_to'] as $handle) {
            if (isset($connection[$handle]) && is_array($connection[$handle])) {
                $connection[$handle] = implode(', ', $connection[$handle]);
            }
        }

        return $connection;
    }

    public function render(): VueComponent
    {
        return VueComponent::render('email-connector', [
            ...$this->blueprintProps(),
            'previewUrl' => cp_route('forms.connect.email.preview', $this->form()->handle()),
        ]);
    }

    protected function connectionFields(array $connection): Fields
    {
        return parent::connectionFields($this->convertLegacyAddresses($connection));
    }

    private function convertLegacyAddresses(array $config): array
    {
        foreach (['to', 'cc', 'bcc', 'reply_to'] as $handle) {
            if (isset($config[$handle]) && is_string($config[$handle])) {
                $config[$handle] = array_map(trim(...), $this->splitAddresses($config[$handle]));
            }
        }

        return $config;
    }

    private function splitAddresses(string $value): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $quote = null;

        for ($i = 0; $i < strlen($value); $i++) {
            $char = $value[$i];
            $pair = substr($value, $i, 2);

            if ($quote) {
                if ($char === '\\') {
                    $current .= $pair;
                    $i++;

                    continue;
                }

                if ($char === $quote) {
                    $quote = null;
                }
            } elseif ($pair === '{{' || ($pair === '}}' && $depth > 0)) {
                $depth += $pair === '{{' ? 1 : -1;
                $current .= $pair;
                $i++;

                continue;
            } elseif ($depth > 0 && ($char === '"' || $char === "'")) {
                $quote = $char;
            } elseif ($char === ',' && $depth === 0) {
                $parts[] = $current;
                $current = '';

                continue;
            }

            $current .= $char;
        }

        return [...$parts, $current];
    }

    protected function connectionRules(): array
    {
        $templateSyntax = new EmailConnectionTemplateSyntax($this->form());

        return [
            'id' => [$templateSyntax],
            'to' => [new EmailConnectionAddress($this->form()), $templateSyntax],
            'cc' => [new EmailConnectionAddress($this->form()), $templateSyntax],
            'bcc' => [new EmailConnectionAddress($this->form()), $templateSyntax],
            'from' => [new EmailConnectionAddress($this->form()), $templateSyntax],
            'reply_to' => [new EmailConnectionAddress($this->form()), $templateSyntax],
            'subject' => [$templateSyntax],
            'html' => [new EmailConnectionView, $templateSyntax],
            'text' => [new EmailConnectionView, $templateSyntax],
            'mailer' => [$templateSyntax],
        ];
    }

    protected function processConnection(array $connection): array
    {
        $values = parent::processConnection($connection);

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

    public function blueprint(): \Statamic\Fields\Blueprint
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
                                        'form' => $this->form()->handle(),
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
                                        'form' => $this->form()->handle(),
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
                                        'form' => $this->form()->handle(),
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
                                        'form' => $this->form()->handle(),
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
                                        'form' => $this->form()->handle(),
                                        'prefix' => 'field:',
                                        'taggable' => true,
                                        'multiple' => true,
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'subject',
                                    'field' => [
                                        'type' => 'autocomplete_editor',
                                        'display' => __('Subject'),
                                        'markdown' => false,
                                        'options' => $this->mentionOptions(),
                                    ],
                                ],
                                [
                                    'handle' => 'body',
                                    'field' => [
                                        'type' => 'autocomplete_editor',
                                        'display' => __('Message'),
                                        'options' => $this->mentionOptions(),
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
