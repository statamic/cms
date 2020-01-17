<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Statamic\Fields\FieldtypeRepository;

class FieldsController extends CpController
{
    public function __construct()
    {
        $this->middleware('can:configure fields');
    }

    public function index(Request $request)
    {
        return view('statamic::fields.index', [
            'blueprints' => Blueprint::all(),
            'fieldsets' => Fieldset::all(),
        ]);
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
            'meta' => $fields->meta()
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
            ->addValues($request->values)
            ->process();

        $values = array_merge($request->values, $fields->values()->all());

        return $values;
    }

    protected function blueprint($blueprint)
    {
        return $blueprint->ensureField('listable', ['type' => 'select', 'cast_booleans' => true]);
    }
}
