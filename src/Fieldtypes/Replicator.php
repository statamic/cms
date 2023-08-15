<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\GraphQL\Types\ReplicatorSetsType;
use Statamic\GraphQL\Types\ReplicatorSetType;
use Statamic\Query\Scopes\Filters\Fields\Replicator as ReplicatorFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Replicator extends Fieldtype
{
    protected $categories = ['structured'];
    protected $defaultValue = [];
    protected $rules = ['array'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'collapse' => [
                        'display' => __('Collapse'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.collapse'),
                        'type' => 'select',
                        'cast_booleans' => true,
                        'options' => [
                            'false' => __('statamic::fieldtypes.replicator.config.collapse.disabled'),
                            'true' => __('statamic::fieldtypes.replicator.config.collapse.enabled'),
                            'accordion' => __('statamic::fieldtypes.replicator.config.collapse.accordion'),
                        ],
                        'default' => false,
                    ],
                    'previews' => [
                        'display' => __('Field Previews'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.previews'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'max_sets' => [
                        'display' => __('Max Sets'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.max_sets'),
                        'type' => 'integer',
                    ],
                    'fullscreen' => [
                        'display' => __('Allow Fullscreen Mode'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.fullscreen'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
            ],
            [
                'display' => __('Manage Sets'),
                'instructions' => __('statamic::fieldtypes.replicator.config.sets'),
                'fields' => [
                    'sets' => [
                        'display' => __('Sets'),
                        'type' => 'sets',
                        'hide_display' => true,
                        'full_width_setting' => true,
                    ],
                ],
            ],
        ];
    }

    public function filter()
    {
        return new ReplicatorFilter($this);
    }

    public function process($data)
    {
        return collect($data)->map(function ($row) {
            return $this->processRow($row);
        })->all();
    }

    protected function processRow($row)
    {
        $fields = $this->fields($row['type'])->addValues($row)->process()->values()->all();

        $row = array_merge([RowId::handle() => Arr::pull($row, '_id')], $row, $fields);

        return Arr::removeNullValues($row);
    }

    public function preProcess($data)
    {
        return collect($data)->map(function ($row, $i) {
            return $this->preProcessRow($row, $i);
        })->all();
    }

    protected function preProcessRow($row, $index)
    {
        $fields = $this->fields($row['type'])->addValues($row)->preProcess()->values()->all();

        $id = Arr::pull($row, RowId::handle()) ?? RowId::generate();

        return array_merge($row, $fields, [
            '_id' => $id,
            'enabled' => $row['enabled'] ?? true,
        ]);
    }

    public function fields($set)
    {
        return new Fields(
            Arr::get($this->flattenedSetsConfig(), "$set.fields"),
            $this->field()->parent(),
            $this->field()
        );
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($set, $index) {
            return $this->setRules($set['type'], $set, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function setRules($handle, $data, $index)
    {
        $rules = $this
            ->fields($handle)
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->setRuleFieldPrefix($index).'.',
            ])
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return [$this->setRuleFieldPrefix($index).'.'.$handle => $rules];
        })->all();
    }

    protected function setRuleFieldPrefix($index)
    {
        return "{$this->field->handle()}.{$index}";
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->field->value())->map(function ($set, $index) {
            return $this->setValidationAttributes($set['type'], $set, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function setValidationAttributes($handle, $data, $index)
    {
        $attributes = $this->fields($handle)->addValues($data)->validator()->attributes();

        return collect($attributes)->mapWithKeys(function ($attribute, $handle) use ($index) {
            return [$this->setRuleFieldPrefix($index).'.'.$handle => $attribute];
        })->all();
    }

    public function augment($values)
    {
        return $this->performAugmentation($values, false);
    }

    public function shallowAugment($values)
    {
        return $this->performAugmentation($values, true);
    }

    protected function performAugmentation($values, $shallow)
    {
        return collect($values)->reject(function ($set, $key) {
            return array_get($set, 'enabled', true) === false;
        })->map(function ($set) use ($shallow) {
            if (! Arr::get($this->flattenedSetsConfig(), "{$set['type']}.fields")) {
                return $set;
            }

            $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

            $values = $this->fields($set['type'])->addValues($set)->{$augmentMethod}()->values();

            return new Values($values->merge([RowId::handle() => $set[RowId::handle()] ?? null, 'type' => $set['type']])->all());
        })->values()->all();
    }

    public function preload()
    {
        $existing = collect($this->field->value())->mapWithKeys(function ($set) {
            $config = Arr::get($this->flattenedSetsConfig(), "{$set['type']}.fields", []);

            return [$set['_id'] => (new Fields($config))->addValues($set)->meta()->put('_', '_')];
        })->toArray();

        $defaults = collect($this->flattenedSetsConfig())->map(function ($set) {
            return (new Fields($set['fields']))->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = collect($this->flattenedSetsConfig())->map(function ($set, $handle) use ($defaults) {
            return (new Fields($set['fields']))->addValues($defaults[$handle])->meta()->put('_', '_');
        })->toArray();

        $previews = collect($existing)->map(function ($fields) {
            return collect($fields)->map(function () {
                return null;
            })->all();
        })->all();

        return [
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
            'collapsed' => [],
            'previews' => $previews,
        ];
    }

    public function flattenedSetsConfig()
    {
        $sets = collect($this->config('sets'));

        // If the first set doesn't have a nested "set" key, it would be the legacy format.
        // We'll put it in a "main" group so it's compatible with the new format.
        // This also happens in the "sets" fieldtype.
        if (! Arr::has($sets->first(), 'sets')) {
            $sets = collect([
                'main' => [
                    'sets' => $sets->all(),
                ],
            ]);
        }

        return $sets->flatMap(function ($section) {
            return $section['sets'];
        });
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type($this->gqlSetsTypeName()));
    }

    public function addGqlTypes()
    {
        $types = collect($this->flattenedSetsConfig())
            ->each(function ($set, $handle) {
                $this->fields($handle)->all()->each(function ($field) {
                    $field->fieldtype()->addGqlTypes();
                });
            })
            ->map(function ($config, $handle) {
                $type = new ReplicatorSetType($this, $this->gqlSetTypeName($handle), $handle);

                return [
                    'handle' => $handle,
                    'name' => $type->name,
                    'type' => $type,
                ];
            })->values();

        GraphQL::addTypes($types->pluck('type', 'name')->all());

        $union = new ReplicatorSetsType($this, $this->gqlSetsTypeName(), $types);

        GraphQL::addType($union);
    }

    protected function gqlSetTypeName($set)
    {
        return 'Set_'.collect($this->field->handlePath())->map(function ($part) {
            return Str::studly($part);
        })->join('_').'_'.Str::studly($set);
    }

    protected function gqlSetsTypeName()
    {
        return 'Sets_'.collect($this->field->handlePath())->map(function ($part) {
            return Str::studly($part);
        })->join('_');
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->map(function ($values) {
            $processed = $this->fields($values['type'])
                ->addValues($values)
                ->preProcessValidatables()
                ->values()
                ->all();

            return array_merge($values, $processed);
        })->all();
    }

    public function toQueryableValue($value)
    {
        return empty($value) ? null : $value;
    }
}
