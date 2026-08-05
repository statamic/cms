<?php

namespace Statamic\Forms\Connections;

use Illuminate\Routing\Router;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\User;
use Statamic\Forms\Connections\Webhooks\SendWebhook;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\Forms\Connections\WebhookConnectionController;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Webhook extends Connection
{
    protected $icon = 'globe-arrow';
    protected $developer = 'Statamic';

    public function description(): ?string
    {
        return __('statamic::messages.webhook_connection_description');
    }

    public function count(Form $form): ?int
    {
        return count($form->connections()->get('webhook', []));
    }

    public function finalized(Submission $submission): object|array
    {
        return collect($submission->form()->connections()->get('webhook'))
            ->reject(fn (array $config) => ($config['enabled'] ?? true) === false)
            ->filter(fn (array $config) => ConnectionLogic::passes($config, $submission))
            ->map(fn (array $config) => new SendWebhook($submission, $submission->site(), $config))
            ->all();
    }

    public function render(Form $form): VueComponent
    {
        $fields = static::blueprint($form)->fields();
        $blank = $fields->preProcess();

        return VueComponent::render('webhook-connection', [
            'action' => cp_route('forms.connect.webhook.update', $form->handle()),
            'blueprint' => static::blueprint($form)->toPublishArray(),
            'rows' => collect($form->connections()->get('webhook'))
                ->map(function (array $config) use ($fields) {
                    $row = $fields->addValues($config)->preProcess();

                    return ['values' => $row->values()->all(), 'meta' => $row->meta()->all()];
                })
                ->all(),
            'defaults' => ['values' => $blank->values()->all(), 'meta' => $blank->meta()->all()],
            'examplePayload' => $this->examplePayload($form),
        ]);
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
                                        'instructions' => __('statamic::messages.webhook_connection_url_instructions'),
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

    public function routes(Router $router): void
    {
        $router->patch('/', [WebhookConnectionController::class, 'update'])->name('update');
    }

    private function examplePayload(Form $form): array
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
