<?php

namespace Statamic\Fieldtypes;

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
            return ['form' => null, 'types' => [], 'components' => []];
        }

        return [
            'form' => ['handle' => $form->handle(), 'title' => __($form->title())],
            'types' => FormConnector::all()->map(fn (Connector $connector): array => [
                'handle' => $connector->handle(),
                'title' => $connector->title(),
                'description' => $connector->description(),
                'icon' => $connector->icon(),
                'developer' => $connector->developer(),
                'count' => $connector->forForm($form)->count(),
                'action' => cp_route('forms.connect.validate', [$form->handle(), $connector->handle()]),
            ])->values()->all(),
            'components' => FormConnector::all()->mapWithKeys(fn (Connector $connector): array => [
                $connector->handle() => $connector->forForm($form)->render(),
            ])->all(),
        ];
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
