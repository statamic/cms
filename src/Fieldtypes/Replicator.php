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
            'collapse' => [
                'display' => __('Collapse'),
                'instructions' => __('statamic::fieldtypes.replicator.config.collapse'),
                'type' => 'select',
                'cast_booleans' => true,
                'width' => 33,
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
                'width' => 33,
                'default' => true,
            ],
            'max_sets' => [
                'display' => __('Max Sets'),
                'instructions' => __('statamic::fieldtypes.replicator.config.max_sets'),
                'type' => 'integer',
                'width' => 33,
            ],
            'sets' => [
                'type' => 'sets',
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

        $row = array_merge(['id' => Arr::pull($row, '_id')], $row, $fields);

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

        $id = Arr::pull($row, 'id') ?? RowId::generate();

        return array_merge($row, $fields, [
            '_id' => $id,
            'enabled' => $row['enabled'] ?? true,
        ]);
    }

    public function fields($set)
    {
        return new Fields(
            $this->config("sets.$set.fields"),
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
            if (! $this->config("sets.{$set['type']}.fields")) {
                return $set;
            }

            $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

            $values = $this->fields($set['type'])->addValues($set)->{$augmentMethod}()->values();

            return new Values($values->merge(['id' => $set['id'] ?? null, 'type' => $set['type']])->all());
        })->values()->all();
    }

    public function preload()
    {
        $existing = collect($this->field->value())->mapWithKeys(function ($set) {
            $config = $this->config("sets.{$set['type']}.fields", []);

            return [$set['_id'] => (new Fields($config))->addValues($set)->meta()->put('_', '_')];
        })->toArray();

        $defaults = collect($this->config('sets'))->map(function ($set) {
            return (new Fields($set['fields']))->all()->map(function ($field) {
                return $field->fieldtype()->preProcess($field->defaultValue());
            })->all();
        })->all();

        $new = collect($this->config('sets'))->map(function ($set, $handle) use ($defaults) {
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

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type($this->gqlSetsTypeName()));
    }

    public function addGqlTypes()
    {
        $types = collect($this->config('sets'))
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
