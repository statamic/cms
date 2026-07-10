<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades;
use Statamic\Facades\Data;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fields;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;

class ReplicatorSetController extends CpController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            'field' => ['required', 'string'],
            'set' => ['required', 'string'],
        ]);

        try {
            $tokenPayload = decrypt($request->token);
        } catch (DecryptException $e) {
            abort(403);
        }

        if (! is_array($tokenPayload)
            || ! is_string($tokenPayload['fqh'] ?? null)
            || ($tokenPayload['user_id'] ?? null) !== User::current()?->id()) {
            abort(403);
        }

        $blueprint = Facades\Blueprint::find($tokenPayload['fqh']);

        if (! $blueprint) {
            throw new NotFoundHttpException();
        }

        $field = $this->getReplicatorField($blueprint, $request->field);

        $replicatorSet = $this->flattenSets($field->get('sets'))[$request->set];

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
        $isGroupOrGrid = isset($config['type']) && in_array($config['type'], ['group', 'grid']);
        $isReplicator = isset($config['type']) && in_array($config['type'], ['bard', 'replicator']);

        if ($isReplicator) {
            $flattenedSets = $this->flattenSets($config['sets'] ?? []);

            if (count($remainingFieldPathComponents) === 1) {
                return $config;
            }

            array_shift($remainingFieldPathComponents);

            return $this->getConfig($flattenedSets, $remainingFieldPathComponents);
        }

        if ($isGroupOrGrid) {
            array_shift($remainingFieldPathComponents);

            $fields = $this->resolveFields($config['fields'] ?? []);

            return $this->getConfig($fields[$remainingFieldPathComponents[0]]['field'], $remainingFieldPathComponents);
        }

        $fields = $this->resolveFields($config[$remainingFieldPathComponents[0]]['fields']);

        array_shift($remainingFieldPathComponents);

        return $this->getConfig($fields[$remainingFieldPathComponents[0]]['field'], $remainingFieldPathComponents);
    }

    private function flattenSets(array $sets): array
    {
        if (! Arr::has(Arr::first($sets), 'sets')) {
            return $sets;
        }

        return collect($sets)
            ->flatMap(fn (array $setGroup): array => $setGroup['sets'] ?? [])
            ->all();
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
