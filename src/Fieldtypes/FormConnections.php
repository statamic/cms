<?php

namespace Statamic\Fieldtypes;

use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Form as Forms;
use Statamic\Facades\FormConnection;
use Statamic\Fields\Fieldtype;
use Statamic\Forms\Connections\Connection;

class FormConnections extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        $form = $this->form();

        return [
            'form' => ['handle' => $form->handle(), 'title' => $form->title()],
            'types' => FormConnection::all()->map(fn (Connection $connection): array => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
                'developer' => $connection->developer(),
                'count' => $connection->count($form),
                'action' => cp_route('forms.connect.update', [$form->handle(), $connection->handle()]),
            ])->values()->all(),
            'components' => FormConnection::all()->mapWithKeys(fn (Connection $connection): array => [
                $connection->handle() => $connection->render($form),
            ])->all(),
        ];
    }

    public function preProcess($value)
    {
        $form = $this->form();

        return collect($value)
            ->map(fn ($config, $handle) => FormConnection::find($handle)?->preProcess($config, $form))
            ->filter()
            ->all();
    }

    public function process($value)
    {
        $form = $this->form();

        $processed = collect($value)
            ->map(fn ($config, $handle) => FormConnection::find($handle)?->process($config, $form))
            ->filter()
            ->all();

        return $processed ?: null;
    }

    private function form(): ?Form
    {
        return Forms::find($this->config('form'));
    }
}
