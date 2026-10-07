<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Rules\WebhookConnectionUrl;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Statamic\Forms\Fields\FormField;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Webhook extends Connection
{
    protected $developer = 'Statamic';

    public function description(): ?string
    {
        return __('statamic::messages.webhook_connection_description');
    }

    public function icon(): ?string
    {
        return Statamic::svg('forms/connect/webhook');
    }

    public function smallIcon(): ?string
    {
        return Statamic::svg('forms/connect/webhook-small');
    }

    protected function job(Submission $submission, array $row): ?object
    {
        return new SendWebhook($submission, $submission->site(), $row);
    }

    public function render(Form $form): VueComponent
    {
        $blueprint = static::blueprint($form);
        $fields = $blueprint->fields()->preProcess();

        return VueComponent::render('webhook-connection', [
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => collect($form->connections()->get('webhook'))
                ->mapWithKeys(fn (array $config): array => [
                    $config['id'] => $fields->addValues($config)->preProcess()->meta()->all(),
                ])
                ->all(),
            'defaults' => [
                'values' => $fields->values()->all(),
                'meta' => $fields->meta()->all(),
            ],
            'examplePayload' => $this->examplePayload($form),
        ]);
    }

    protected function preProcessRow(array $row, Form $form): array
    {
        return static::blueprint($form)->fields()
            ->addValues($row)
            ->preProcess()
            ->values()
            ->all();
    }

    protected function rowRules(Form $form): array
    {
        return [
            'url' => ['required', 'url:http,https', new WebhookConnectionUrl],
            'verify_ssl' => ['nullable', 'boolean'],
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
            'verify_ssl' => Arr::get($values, 'verify_ssl') === false ? false : null,
        ];
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

    private function examplePayload(Form $form): string
    {
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
