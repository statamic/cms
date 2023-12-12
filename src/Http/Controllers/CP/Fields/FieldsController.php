<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Statamic\Facades\Blueprint;
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
        $reserved = [
            'content_type',
            'elseif',
            'endif',
            'endunless',
            'if',
            'length',
            'reference',
            'resource',
            'status',
            'unless',
            'value', // todo: can be removed when https://github.com/statamic/cms/issues/2495 is resolved
            'views',
        ];

        $fields = collect([
            'display' => [
                'display' => __('Display Label'),
                'instructions' => __('statamic::messages.fields_display_instructions'),
                'type' => 'field_display',
            ],
            'handle' => [
                'display' => __('Handle'),
                'instructions' => __('statamic::messages.fields_handle_instructions'),
                'type' => 'slug',
                'from' => 'display',
                'separator' => '_',
                'validate' => 'required|regex:/^[a-zA-Z][a-zA-Z0-9_]*$/|not_in:'.implode(',', $reserved),
                'show_regenerate' => true,
            ],
            'instructions' => [
                'display' => __('Instructions'),
                'instructions' => __('statamic::messages.fields_instructions_instructions'),
                'type' => 'textarea',
            ],
            'instructions_position' => [
                'display' => __('Instructions Position'),
                'instructions' => __('statamic::messages.fields_instructions_position_instructions'),
                'type' => 'select',
                'options' => [
                    'above' => __('Above'),
                    'below' => __('Below'),
                ],
                'default' => 'above',
                'if' => [
                    'instructions' => 'not null',
                ],
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
                'unless' => [
                    'type' => 'section',
                ],
            ],
            'visibility' => [
                'display' => __('Visibility'),
                'instructions' => __('statamic::messages.fields_visibility_instructions'),
                'options' => [
                    'visible' => __('Visible'),
                    'read_only' => __('Read Only'),
                    'computed' => __('Computed'),
                    'hidden' => __('Hidden'),
                ],
                'default' => 'visible',
                'type' => 'select',
            ],
            'replicator_preview' => [
                'display' => __('Preview'),
                'instructions' => __('statamic::messages.fields_replicator_preview_instructions'),
                'type' => 'toggle',
                'validate' => 'boolean',
                'default' => true,
            ],
            'duplicate' => [
                'display' => __('Duplicate'),
                'instructions' => __('statamic::messages.fields_duplicate_instructions'),
                'type' => 'toggle',
                'validate' => 'boolean',
                'default' => true,
            ],
        ])->map(fn ($field, $handle) => compact('handle', 'field'))->values()->all();

        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => array_merge(
                        [[
                            'fields' => $fields,
                            'display' => __('Common'),
                        ]],
                        $blueprint->contents()['tabs']['main']['sections'],
                    ),
                ],
            ],
        ]);
    }
}
