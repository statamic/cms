<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Connections\Connection;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormConnectController extends CpController
{
    use ProvidesFormAbilities;

    public function index($form)
    {
        $this->authorize('edit', $form);

        return Inertia::render('forms/connect/Index', [
            'form' => $form,
            'can' => $this->formAbilities($form),
            'connections' => FormConnection::all()->map(fn (Connection $connection): array => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
                'developer' => $connection->developer(),
                'count' => $connection->count($form),
                'url' => cp_route('forms.connect.show', [$form->handle(), $connection->handle()]),
            ])->values(),
        ]);
    }

    public function show($form, $type)
    {
        $this->authorize('edit', $form);

        throw_unless($connection = FormConnection::find($type), NotFoundHttpException::class);

        return Inertia::render('forms/connect/Show', [
            'form' => $form,
            'can' => $this->formAbilities($form),
            'connection' => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
            ],
            'component' => $connection->render($form),
            'config' => collect($form->connections()->get($type, []))
                ->map(fn ($config) => array_merge($config, ['_id' => $config['id'] ?? Str::random(8)]))
                ->all(),
            'suggestableFields' => $this->suggestableFields($form),
        ]);
    }

    private function suggestableFields($form): array
    {
        return $form->formFields()->fields()
            ->map(fn ($field) => [
                'handle' => $field->handle(),
                'icon' => $field->fieldtype()->icon(),
                'category' => $field->fieldtype()->categories()[0] ?? 'other',
                'config' => Arr::removeNullValues([
                    'type' => $field->type(),
                    'display' => $field->display(),
                    'options' => Arr::get($field->config(), 'options'),
                ]),
            ])
            ->reject(fn ($field) => in_array($field['category'], ['information', 'structure']))
            ->values()
            ->all();
    }
}
