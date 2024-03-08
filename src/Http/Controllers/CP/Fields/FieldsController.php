<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Field;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Middleware\CP\CanManageBlueprints;

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

        $blueprint = $this
            ->blueprint($fieldtype->configBlueprint())
            ->ensureField('hide_display', ['type' => 'toggle', 'visibility' => 'hidden']);

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
            'type' => 'required',
            'values' => 'required|array',
        ]);

        $fieldtype = FieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values);

        $extraRules = [];
        $customMessages = [
            'handle.not_in' => __('statamic::validation.reserved'),
        ];

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
