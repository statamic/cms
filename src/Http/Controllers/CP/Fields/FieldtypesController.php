<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class FieldtypesController extends CpController
{
    public function index(Request $request)
    {
        $request->validate(['context' => 'sometimes|array']);

        $context = $request->input('context', []);

        if ($context) {
            $context['field_path'] ??= [];
        }

        if (array_key_exists('nested', $context)) {
            $context['nested'] = $request->boolean('context.nested');
        }

        $fieldtypes = app('statamic.fieldtypes')
            ->unique() // Remove any dupes in the case of aliases. Aliases are defined later so they will win.
            ->map(function ($class) use ($context) {
                return app($class)->context($context);
            });

        if ($request->selectable) {
            $selectableMethod = $request->forms ? 'selectableInForms' : 'selectable';
            $fieldtypes = $fieldtypes->filter->$selectableMethod()->filter->isVisible();
        }

        return $fieldtypes->sortBy->handle()->values();
    }
}
