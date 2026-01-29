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

        $config = $blueprint->fields()->all()->get($remainingFieldPathComponents[0])->config();

        $config = $this->getConfig($config, $remainingFieldPathComponents);

        if (! isset($config['type'])) {
            throw new \Exception("Cannot find Replicator field [$field]");
        }

        return new Field(Str::afterLast($field, '.'), $config);
    }

    private function getConfig(array $config, array $remainingFieldPathComponents): array
    {
        $isReplicator = isset($config['type']) && in_array($config['type'], ['bard', 'replicator']);

        if ($isReplicator) {
            $flattenedSets = collect($config['sets'])
                ->flatMap(fn (array $setGroup): array => $setGroup['sets'] ?? [])
                ->all();

            if (count($remainingFieldPathComponents) === 1) {
                return $config;
            }

            array_shift($remainingFieldPathComponents);

            return $this->getConfig($flattenedSets, $remainingFieldPathComponents);
        }

        $fields = $this->resolveFields($config[$remainingFieldPathComponents[0]]['fields']);

        array_shift($remainingFieldPathComponents);

        return $this->getConfig($fields[$remainingFieldPathComponents[0]]['field'], $remainingFieldPathComponents);
    }

    private function resolveFields(array $fields): array
    {
        return collect($fields)
            ->flatMap(function ($field): array {
                if (isset($field['import']) || (isset($field['field']) && is_string($field['field']))) {
                    return (new Fields([$field]))
                        ->all()
                        ->map(fn (Field $field) => [
                            'handle' => $field->handle(),
                            'field' => $field->config(),
                        ])
                        ->all();
                }

                return [$field];
            })
            ->keyBy('handle')
            ->all();
    }
}
