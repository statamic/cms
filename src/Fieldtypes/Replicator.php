<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\Blink;
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
    use AddsEntryValidationReplacements;

    protected $categories = ['structured'];
    protected $keywords = ['builder', 'page builder', 'content'];
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
                    'button_label' => [
                        'display' => __('Add Set Label'),
                        'instructions' => __('statamic::fieldtypes.replicator.config.button_label'),
                        'type' => 'text',
                        'default' => '',
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
        return collect($data)->map(function ($row, $i) {
            return $this->processRow($row, $i);
        })->all();
    }

    protected function processRow($row, $index)
    {
        $fields = $this->fields($row['type'], $index)->addValues($row)->process()->values()->all();

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
        $fields = $this->fields($row['type'], $index)->addValues($row)->preProcess()->values()->all();

        $id = Arr::pull($row, RowId::handle()) ?? RowId::generate();

        return array_merge($row, $fields, [
            '_id' => $id,
            'enabled' => $row['enabled'] ?? true,
        ]);
    }

    public function fields($set, $index = -1)
    {
        $config = Arr::get($this->flattenedSetsConfig(), "$set.fields");
        $hash = md5($this->field->fieldPathPrefix().$index.json_encode($config));

        return Blink::once($hash, function () use ($config, $index) {
            return new Fields(
                $config,
                $this->field()->parent(),
                $this->field(),
                $index
            );
        });
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
            ->fields($handle, $index)
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->setRuleFieldPrefix($index).'.',
            ]);

        $rules = $this
            ->addEntryValidationReplacements($this->field, $rules)
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
        $attributes = $this->fields($handle, $index)->addValues($data)->validator()->attributes();

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
            return Arr::get($set, 'enabled', true) === false;
        })->map(function ($set, $index) use ($shallow) {
            if (! Arr::get($this->flattenedSetsConfig(), "{$set['type']}.fields")) {
                return $set;
            }

            $augmentMethod = $shallow ? 'shallowAugment' : 'augment';

            $values = $this->fields($set['type'], $index)->addValues($set)->{$augmentMethod}()->values();

            return new Values($values->merge([RowId::handle() => $set[RowId::handle()] ?? null, 'type' => $set['type']])->all());
        })->values()->all();
    }

    public function preload()
    {
        $existing = collect($this->field->value())->mapWithKeys(function ($set, $index) {
            return [$set['_id'] => $this->fields($set['type'], $index)->addValues($set)->meta()->put('_', '_')];
        })->toArray();

        $blink = md5(json_encode($this->flattenedSetsConfig()));

        $defaults = Blink::once($blink.'-defaults', function () {
            return collect($this->flattenedSetsConfig())->map(function ($set, $handle) {
                return $this->fields($handle)->all()->map(function ($field) {
                    return $field->fieldtype()->preProcess($field->defaultValue());
                })->all();
            })->all();
        });

        $new = Blink::once($blink.'-new', function () use ($defaults) {
            return collect($this->flattenedSetsConfig())->map(function ($set, $handle) use ($defaults) {
                return $this->fields($handle)->addValues($defaults[$handle])->meta()->put('_', '_');
            })->toArray();
        });

        return [
            'existing' => $existing,
            'new' => $new,
            'defaults' => $defaults,
            'collapsed' => [],
        ];
    }

    public function flattenedSetsConfig()
    {
        $blink = md5($this->field?->handle().json_encode($this->field?->config()));

        return Blink::once($blink, function () {
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
        return collect($value)->map(function ($values, $index) {
            $processed = $this->fields($values['type'], $index)
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
