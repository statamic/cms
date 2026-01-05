<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades;
use Statamic\Facades\Data;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Http\Controllers\CP\CpController;

class ReplicatorSetController extends CpController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'blueprint' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            'field' => ['required', 'string'],
            'set' => ['required', 'string'],
        ]);

        $blueprint = Facades\Blueprint::find($request->blueprint);

        if (! $blueprint) {
            throw new NotFoundHttpException();
        }

        $field = $this->getReplicatorField($blueprint, $request->field);

        $replicatorSet = collect($field->get('sets'))
            ->flatMap(fn (array $setGroup) => $setGroup['sets'] ?? [])
            ->get($request->set);

        if (! $replicatorSet) {
            throw new \Exception("Cannot find Replicator set [$request->set]");
        }

        $replicatorFields = new Fields(
            items: $replicatorSet['fields'],
            parent: Data::find($request->reference),
            parentField: $field,
            parentIndex: -1
        );

        $defaults = $replicatorFields->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        })->all();

        $new = $replicatorFields->addValues($defaults)->meta()->put('_', '_')->toArray();

        return compact('new', 'defaults');
    }

    private function getReplicatorField(Blueprint $blueprint, string $field): Field
    {
        $remainingFieldPathComponents = explode('.', $field);

        $config = $blueprint->field($remainingFieldPathComponents[0])->config();
        unset($remainingFieldPathComponents[0]);

        foreach ($remainingFieldPathComponents as $index => $fieldPathComponent) {
            unset($remainingFieldPathComponents[$fieldPathComponent]);

            if (isset($config['sets'])) {
                $config = collect($config['sets'])
                    ->flatMap(fn (array $setGroup): array => $setGroup['sets'] ?? [])
                    ->get($fieldPathComponent);

                continue;
            }

            if (isset($config['fields'])) {
                $config = collect($config['fields'])
                    ->where('handle', $remainingFieldPathComponents[$index])
                    ->first()['field'] ?? null;

                continue;
            }

            throw new \Exception("Cannot resolve field path component [$fieldPathComponent]");
        }

        if (! isset($config['type'])) {
            throw new \Exception("Cannot find replicator field [$field]");
        }

        return new Field(Str::afterLast($field, '.'), $config);
    }
}
