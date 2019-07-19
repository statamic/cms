<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Statamic\Fields\FieldtypeRepository;

class FieldsController extends CpController
{
    public function index(Request $request)
    {
        return view('statamic::fields.index');
    }

    public function edit(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'values' => 'array',
        ]);

        $fieldtype = FieldtypeRepository::find($request->type);

        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($request->values)
            ->preProcess();

        return [
            'fieldtype' => $fieldtype->toArray(),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => array_merge($request->values, $fields->values()),
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

        $blueprint = $fieldtype->configBlueprint();

        $fields = $blueprint
            ->fields()
            ->addValues($request->values)
            ->process();

        return array_merge($request->values, $fields->values());
    }
}
