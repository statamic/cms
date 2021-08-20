<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class FieldsController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
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
            'type' => 'required',
            'values' => 'required|array',
        ]);

        $fieldtype = FieldtypeRepository::find($request->type);

        $blueprint = $this->blueprint($fieldtype->configBlueprint());

        $fields = $blueprint
            ->fields()
            ->addValues($request->values);

        $fields->validate([], [
            'handle.not_in' => __('statamic::validation.reserved'),
        ]);

        $values = array_merge($request->values, $fields->process()->values()->all());

        return $values;
    }

    protected function blueprint($blueprint)
    {
        $reserved = [
            'content_type',
            'elseif',
            'endif',
            'endunless',
            'if',
            'length',
            'reference',
            'resource',
            'unless',
            'value', // todo: can be removed when https://github.com/statamic/cms/issues/2495 is resolved
        ];

        $prepends = collect([
            'display' => [
                'display' => __('Display'),
                'instructions' => __('statamic::messages.fields_display_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
            'handle' => [
                'display' => __('Handle'),
                'instructions' => __('statamic::messages.fields_handle_instructions'),
                'type' => 'text',
                'validate' => 'required|not_in:'.implode(',', $reserved),
                'width' => 50,
            ],
            'instructions' => [
                'display' => __('Instructions'),
                'instructions' => __('statamic::messages.fields_instructions_instructions'),
                'type' => 'text',
            ],
            'instructions_position' => [
                'display' => __('Instructions Position'),
                'instructions' => __('statamic::messages.fields_instructions_position_instructions'),
                'type' => 'select',
                'options' => [
                    'above' => __('Above the field'),
                    'below' => __('Below the field'),
                ],
                'default' => 'above',
                'width' => 50,
            ],
            'listable' => [
                'display' => __('Listable'),
                'instructions' => __('statamic::messages.fields_listable_instructions'),
                'type' => 'select',
                'cast_booleans' => true,
                'options' => [
                    'hidden' => __('Hidden by default'),
                    'true' => __('Shown by default'),
                    'false' => __('Not listable'),
                ],
                'default' => 'hidden',
                'width' => 50,
                'unless' => [
                    'type' => 'section',
                ],
            ],
        ]);

        foreach ($prepends->reverse() as $handle => $prepend) {
            $blueprint->ensureFieldPrepended($handle, $prepend);
        }

        return $blueprint;
    }
}
