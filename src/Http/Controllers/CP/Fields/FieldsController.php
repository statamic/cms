<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\CanManageBlueprints;
use Statamic\Support\Str;

use function Statamic\trans as __;

class FieldsController extends CpController
{
    public function __construct()
    {
        $this->middleware(CanManageBlueprints::class);
    }

    public function index(Request $request)
    {
        return redirect(cp_route('blueprints.index'));
    }

    public function edit(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'values' => 'array',
        ]);

        $fieldtype = FieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values)
            ->preProcess();

        return [
            'fieldtype' => $fieldtype->toArray(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => array_merge($request->values, $fields->values()->all()),
            'meta' => $fields->meta(),
        ];
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'nullable',
            'type' => 'required',
            'values' => 'required|array',
            'fields' => 'sometimes|array',
            'isInsideSet' => 'sometimes|boolean',
        ]);

        $fieldtype = FieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values);

        $extraRules = [
            'handle' => [
                function ($attribute, $value, $fail) use ($request) {
                    $existingFieldWithHandle = collect($request->fields ?? [])
                        ->when($request->has('id'), fn ($collection) => $collection->reject(fn ($field) => $field['_id'] === $request->id))
                        ->flatMap(function (array $field) {
                            if ($field['type'] === 'import') {
                                return Fieldset::find($field['fieldset'])->fields()->all()->map->handle()->toArray();
                            }

                            return [$field['handle']];
                        })
                        ->values()
                        ->contains($request->values['handle']);

                    if ($existingFieldWithHandle) {
                        $fail(__('statamic::validation.duplicate_field_handle', ['handle' => $value]));
                    }
                },
            ],
        ];

        $customMessages = [
            'handle.not_in' => __('statamic::validation.reserved'),
        ];

        $referer = $request->headers->get('referer');

        if (Str::contains($referer, 'forms/') && Str::contains($referer, '/blueprint') && $request->values['handle'] === 'date') {
            $extraRules['handle'][] = 'not_in:date';
        }

        if ($request->isInsideSet) {
            $extraRules['handle'][] = 'not_in:type';
        }

        if ($request->type === 'date' && $request->values['handle'] === 'date') {
            $extraRules['mode'] = 'in:single';
            $customMessages['mode.in'] = __('statamic::validation.date_fieldtype_only_single_mode_allowed');
        }

        $fields->validate($extraRules, $customMessages);

        $values = array_merge($request->values, $fields->process()->values()->all());

        return $values;
    }

    protected function blueprint($blueprint)
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => array_merge(
                        [[
                            'fields' => Field::commonFieldOptions()->items(),
                            'display' => __('Common'),
                        ]],
                        $blueprint->contents()['tabs']['main']['sections'],
                    ),
                ],
            ],
        ]);
    }
}
