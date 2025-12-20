<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Fields;
use Statamic\Http\Controllers\CP\CpController;

class ReplicatorSetController extends CpController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'blueprint' => ['required', 'string'],
            'field' => ['required', 'string'],
        ]);

        $blueprint = Blueprint::find($request->blueprint);

        // todo: handle nested replicators
        $replicator = $blueprint->field(Str::before($request->field, '.'));

        $setHandle = Str::afterLast($request->field, '.');
        $setConfig = null;

        foreach ($replicator->get('sets') as $setGroup) {
            if (isset($setGroup['sets'][$setHandle])) {
                $setConfig = $setGroup['sets'][$setHandle];
            }
        }

        if (! $setConfig) {
            throw new \Exception("Couldn't find replicator set.");
        }

        // todo: make sure the $parent and $parentIndex we pass in here is correct
        $replicatorFields = new Fields($setConfig['fields'], parentField: $replicator);

        $defaults = $replicatorFields->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        })->all();

        $new = $replicatorFields->addValues($defaults)->meta()->put('_', '_')->toArray();

        return [
            'new' => $new,
            'defaults' => $defaults,
        ];
    }
}
