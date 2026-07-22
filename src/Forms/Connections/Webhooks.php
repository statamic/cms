<?php

namespace Statamic\Forms\Connections;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\User;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\Forms\Connections\WebhookConnectionController;
use Statamic\Support\VueComponent;

use function Statamic\trans as __;

class Webhooks extends Connection
{
    protected static $handle = 'webhook';

    protected $icon = 'globe-arrow';
    protected $developer = 'Statamic';

    public function description()
    {
        return __('statamic::messages.webhook_connection_description');
    }

    public function count(Form $form): ?int
    {
        return count($form->connections()->get('webhook', []));
    }

    public function render(Form $form): VueComponent
    {
        return VueComponent::render('webhook-connection', [
            'action' => cp_route('forms.connect.webhook.update', $form->handle()),
            'examplePayload' => $this->examplePayload($form),
        ]);
    }

    public function routes($router): void
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
