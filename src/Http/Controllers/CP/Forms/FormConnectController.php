<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\FormConnector;
use Statamic\Forms\Connectors\Connector;
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
            'connectors' => FormConnector::all()->map(fn (Connector $connector): array => [
                'handle' => $connector->handle(),
                'title' => $connector->title(),
                'description' => $connector->description(),
                'icon' => $connector->icon(),
                'developer' => $connector->developer(),
                'count' => $connector->count($form),
                'url' => cp_route('forms.connect.edit', [$form->handle(), $connector->handle()]),
            ])->values(),
        ]);
    }

    public function edit($form, string $handle)
    {
        $this->authorize('edit', $form);

        throw_unless($connector = FormConnector::find($handle), NotFoundHttpException::class);

        return Inertia::render('forms/connect/Edit', [
            'form' => $form,
            'can' => $this->formAbilities($form),
            'connector' => [
                'handle' => $connector->handle(),
                'title' => $connector->title(),
                'description' => $connector->description(),
                'icon' => $connector->smallIcon(),
            ],
            'component' => $connector->render($form),
            'value' => $connector->preProcess($form->connections()->get($connector->handle(), []), $form),
            'action' => cp_route('forms.connect.update', [$form->handle(), $connector->handle()]),
            'isConfigured' => $connector->isConfigured(),
            'suggestableFields' => $this->suggestableFields($form),
        ]);
    }

    public function update(Request $request, $form, string $handle)
    {
        $this->authorize('edit', $form);

        throw_unless($connector = FormConnector::find($handle), NotFoundHttpException::class);

        Validator::make($request->except('_save'), $connector->rules($form))->validate();

        $config = $connector->process($request->except('_save'), $form);

        if ($request->boolean('_save', true)) {
            $form->connections($form->connections()->put($connector->handle(), $config))->save();
        }

        return $connector->preProcess($config, $form);
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
