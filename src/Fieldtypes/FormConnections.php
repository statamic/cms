<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Facades\Validator;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Form as Forms;
use Statamic\Facades\FormConnector;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\Connectors\Connector;

use function Statamic\trans as __;

class FormConnections extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        if (! $form = $this->form()) {
            return ['form' => null, 'types' => []];
        }

        return [
            'form' => ['handle' => $form->handle(), 'title' => __($form->title())],
            'types' => FormConnector::all()->map(fn (Connector $connector): array => [
                'handle' => $connector->handle(),
                'title' => $connector->title(),
                'description' => $connector->description(),
                'icon' => $connector->icon(),
                'developer' => $connector->developer(),
                'action' => cp_route('forms.connect.validate', [$form->handle(), $connector->handle()]),
                'editor' => cp_route('forms.connect.editor', [$form->handle(), $connector->handle()]),
            ])->values()->all(),
        ];
    }

    public function rules(): array
    {
        return [function ($attribute, $value, $fail) {
            if (! $form = $this->form()) {
                return;
            }

            $valid = collect(is_array($value) ? $value : [])->every(function ($connections, $handle) use ($form) {
                if (! $connector = FormConnector::find($handle)?->forForm($form)) {
                    return true;
                }

                return Validator::make(Connector::normalizeConnections($connections), $connector->rules())->passes();
            });

            if (! $valid) {
                $fail(__('statamic::validation.form_connections_invalid'));
            }
        }];
    }

    public function preProcess($value)
    {
        if (! $form = $this->form()) {
            return [];
        }

        return collect($value)
            ->map(fn ($config, $handle) => FormConnector::find($handle)?->setForm($form)->preProcess(Connector::normalizeConnections($config)))
            ->filter()
            ->all();
    }

    public function process($value)
    {
        if (! $form = $this->form()) {
            return null;
        }

        return collect($value)
            ->map(fn ($config, $handle) => FormConnector::find($handle)?->setForm($form)->process(Connector::normalizeConnections($config)))
            ->filter()
            ->all();
    }

    private function form(): ?Form
    {
        return Forms::find($this->config('form'));
    }
}
