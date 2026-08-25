<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\FormConnection;
use Statamic\Forms\Connections\Connection;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;
use Statamic\Support\Arr;

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

    public function show($form, string $connection)
    {
        $this->authorize('edit', $form);

        throw_unless($connection = FormConnection::find($connection), NotFoundHttpException::class);

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
            'value' => $connection->preProcess($form->connections()->get($connection->handle(), []), $form),
            'action' => cp_route('forms.connect.update', [$form->handle(), $connection->handle()]),
            'suggestableFields' => $this->suggestableFields($form),
        ]);
    }

    public function update(Request $request, $form, string $connection)
    {
        $this->authorize('edit', $form);

        throw_unless($connection = FormConnection::find($connection), NotFoundHttpException::class);

        $request->validate($connection->rules($form));

        $config = $connection->process($request->all(), $form);

        $form->connections($form->connections()->put($connection->handle(), $config))->save();

        return $config;
    }

    private function suggestableFields($form): array
    {
        return $form->formFields()->fields()
            ->filter(fn (FormField $field) => $field->fieldtype()->collectsValue())
            ->map(fn (FormField $field) => [
                'handle' => $field->handle(),
                'icon' => $field->fieldtype()->icon(),
                'category' => $field->fieldtype()->categories()[0] ?? 'other',
                'config' => Arr::removeNullValues([
                    'type' => $field->type(),
                    'display' => $field->display(),
                    'options' => Arr::get($field->config(), 'options'),
                ]),
            ])
            ->values()
            ->all();
    }
}
