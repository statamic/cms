<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Blueprint;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Statamic\Forms\Fields\FormField;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
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

    public function breadcrumbIcon(): ?string
    {
        return Statamic::svg('forms/connect/webhook-flat');
    }

    public function count(Form $form): ?int
    {
        return count($form->connections()->get('webhook', []));
    }

    public function finalized(Submission $submission): object|array
    {
        return collect($this->config())
            ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission))
            ->map(fn (array $config) => new SendWebhook($submission, $submission->site(), $config))
            ->values()
            ->all();
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

    public function preProcess(array $config, Form $form): array
    {
        $fields = static::blueprint($form)->fields();

        return collect($config)
            ->map(fn (array $config): array => [
                'id' => $config['id'],
                'enabled' => Arr::get($config, 'enabled') !== false,
                'conditions' => ConnectionLogic::preProcess(Arr::get($config, 'conditions') ?? []),
                ...$fields->addValues($config)->preProcess()->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function rules(Form $form): array
    {
        return [
            '*' => ['array'],
            '*.url' => ['required', 'url:http,https'],
            '*.verify_ssl' => ['nullable', 'boolean'],
            '*.enabled' => ['nullable', 'boolean'],
            '*.conditions' => ['nullable', 'array'],
            '*.conditions.*' => ['array'],
        ];
    }

    public function process(array $data, Form $form): array
    {
        $fields = static::blueprint($form)->fields();

        return collect($data)
            ->map(function (array $config) use ($fields): array {
                $config = Arr::removeNullValues($config);

                $values = $fields
                    ->addValues($config)
                    ->process()
                    ->values()
                    ->all();

                return Arr::removeNullValues([
                    'id' => Arr::get($config, 'id') ?? Str::random(8),
                    ...$values,
                    'enabled' => Arr::get($config, 'enabled') === false ? false : null,
                    'verify_ssl' => Arr::get($values, 'verify_ssl') === false ? false : null,
                    'conditions' => ConnectionLogic::process(Arr::get($config, 'conditions') ?? []),
                ]);
            })
            ->values()
            ->all();
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
