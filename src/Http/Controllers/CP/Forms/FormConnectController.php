<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            'uniqueInstancesEnabled' => $form->hasUniqueInstances(),
            'connections' => FormConnection::all()->map(fn (Connection $connection): array => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
                'developer' => $connection->developer(),
                'count' => $connection->count($form),
                'url' => cp_route('forms.connect.edit', [$form->handle(), $connection->handle()]),
            ])->values(),
        ]);
    }

    public function edit($form, string $connection)
    {
        $this->authorize('edit', $form);

        throw_unless($connection = FormConnection::find($connection), NotFoundHttpException::class);

        return Inertia::render('forms/connect/Edit', [
            'form' => $form,
            'can' => $this->formAbilities($form),
            'uniqueInstancesEnabled' => $form->hasUniqueInstances(),
            'connection' => [
                'handle' => $connection->handle(),
                'title' => $connection->title(),
                'description' => $connection->description(),
                'icon' => $connection->icon(),
            ],
            'component' => $connection->render($form),
            'value' => $connection->preProcess($form->connections()->get($connection->handle(), []), $form),
            'action' => cp_route('forms.connect.update', [$form->handle(), $connection->handle()]),
            'isConfigured' => $connection->isConfigured(),
            'suggestableFields' => $this->suggestableFields($form),
        ]);
    }

    public function update(Request $request, $form, string $connection)
    {
        $this->authorize('edit', $form);

        throw_unless($connection = FormConnection::find($connection), NotFoundHttpException::class);

        Validator::make($request->except('_save'), $connection->rules($form))->validate();

        $config = $connection->process($request->except('_save'), $form);

        if ($request->boolean('_save', true)) {
            $form->connections($form->connections()->put($connection->handle(), $config))->save();
        }

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
