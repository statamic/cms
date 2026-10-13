<?php

namespace Statamic\Forms\Connectors;

use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Forms\Connectors\Rules\WebhookConnectionUrl;
use Statamic\Forms\Connectors\Webhooks\SendWebhook;
use Statamic\Forms\Fields\FormField;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Webhook extends Connector
{
    protected $developer = 'Statamic';

    public function description(): ?string
    {
        return __('statamic::messages.webhook_connector_description');
    }

    public function icon(): ?string
    {
        return Statamic::svg('forms/connect/webhook');
    }

    public function smallIcon(): ?string
    {
        return Statamic::svg('forms/connect/webhook-small');
    }

    protected function job(Submission $submission, array $connection): ?object
    {
        return new SendWebhook($submission, $submission->site(), $connection);
    }

    public function render(): VueComponent
    {
        return VueComponent::render('webhook-connector', [
            ...$this->blueprintProps(),
            'examplePayload' => $this->examplePayload(),
        ]);
    }

    protected function connectionRules(): array
    {
        return [
            'url' => ['url:http,https', new WebhookConnectionUrl],
            'verify_ssl' => ['nullable', 'boolean'],
        ];
    }

    protected function processConnection(array $connection): array
    {
        $values = parent::processConnection($connection);

        return [
            ...$values,
            'verify_ssl' => Arr::get($values, 'verify_ssl') === false ? false : null,
        ];
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
                                    'handle' => 'url',
                                    'field' => [
                                        'type' => 'text',
                                        'input_type' => 'url',
                                        'display' => __('URL'),
                                        'validate' => ['required'],
                                        'placeholder' => 'https://example.com/webhook',
                                    ],
                                ],
                                [
                                    'handle' => 'verify_ssl',
                                    'field' => [
                                        'type' => 'toggle',
                                        'display' => __('Verify SSL Certificate'),
                                        'instructions' => __('statamic::messages.webhook_connection_verify_ssl_instructions'),
                                        'default' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function examplePayload(): string
    {
        $form = $this->form();
        $latestSubmission = null;

        if (User::current()->can('viewSubmissions', $form)) {
            $latestSubmission = $form->querySubmissions()->whereNull('partial')->orderBy('date', 'desc')->first();
        }

        return json_encode([
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
        ], JSON_PRETTY_PRINT);
    }
}
