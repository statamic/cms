<?php

namespace Statamic\Fieldtypes;

use Facades\Statamic\Fieldtypes\RowId;
use Statamic\Facades\GraphQL;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Values;
use Statamic\GraphQL\Types\GridItemType;
use Statamic\Query\Scopes\Filters\Fields\Grid as GridFilter;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class Grid extends Fieldtype
{
    use AddsEntryValidationReplacements;

    protected $categories = ['structured'];
    protected $defaultable = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Fields'),
                'fields' => [
                    'fields' => [
                        'display' => __('Fields'),
                        'instructions' => __('statamic::fieldtypes.grid.config.fields'),
                        'type' => 'fields',
                        'full_width_setting' => true,
                    ],
                ],
            ],
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.grid.config.mode'),
                        'type' => 'select',
                        'options' => [
                            'table' => __('Table'),
                            'stacked' => __('Stacked'),
                        ],
                        'default' => 'table',
                    ],
                    'max_rows' => [
                        'display' => __('Maximum Rows'),
                        'instructions' => __('statamic::fieldtypes.grid.config.max_rows'),
                        'type' => 'integer',
                    ],
                    'min_rows' => [
                        'display' => __('Minimum Rows'),
                        'instructions' => __('statamic::fieldtypes.grid.config.min_rows'),
                        'type' => 'integer',
                    ],
                    'add_row' => [
                        'display' => __('Add Row Label'),
                        'instructions' => __('statamic::fieldtypes.grid.config.add_row'),
                        'type' => 'text',
                    ],
                    'reorderable' => [
                        'display' => __('Reorderable'),
                        'instructions' => __('statamic::fieldtypes.grid.config.reorderable'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'fullscreen' => [
                        'display' => __('Allow Fullscreen Mode'),
                        'instructions' => __('statamic::fieldtypes.grid.config.fullscreen'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                ],
            ],
        ];
    }

    public function filter()
    {
        return new GridFilter($this);
    }

    public function process($data)
    {
        return collect($data)->map(function ($row, $index) {
            return $this->processRow($row, $index);
        })->all();
    }

    private function processRow($row, $index)
    {
        $fields = $this->fields($index)->addValues($row)->process()->values()->all();

        $row = array_merge([RowId::handle() => Arr::pull($row, '_id')], $row, $fields);

        return Arr::removeNullValues($row);
    }

    public function preProcess($data)
    {
        $data = collect($data);

        if ($minRows = $this->config('min_rows')) {
            $data = $data->pad($minRows, []);
        }

        return $data->map(function ($row, $i) {
            return $this->preProcessRow($row, $i);
        })->all();
    }

    private function preProcessRow($row, $index)
    {
        $fields = $this->fields($index)->addValues($row)->preProcess()->values()->all();

        $id = Arr::pull($row, RowId::handle()) ?? RowId::generate();

        return array_merge($row, $fields, [
            '_id' => $id,
        ]);
    }

    public function fields($index = -1)
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field(), $index);
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($min = $this->config('min_rows')) {
            $rules[] = 'min:'.$min;
        }

        if ($max = $this->config('max_rows')) {
            $rules[] = 'max:'.$max;
        }

        return $rules;
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($row, $index) {
            return $this->rowRules($row, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    protected function rowRules($data, $index)
    {
        $rules = $this
            ->fields($index)
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->rowRuleFieldPrefix($index).'.',
            ]);

        $rules = $this
            ->addEntryValidationReplacements($this->field, $rules)
            ->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return [$this->rowRuleFieldPrefix($index).'.'.$handle => $rules];
        })->all();
    }

    protected function rowRuleFieldPrefix($index)
    {
        return "{$this->field->handle()}.{$index}";
    }

    public function extraValidationAttributes(): array
    {
        return collect($this->field->value())->map(function ($row, $index) {
            $attributes = $this->fields($index)->validator()->attributes();

            return collect($attributes)->except('_id')->mapWithKeys(function ($attribute, $handle) use ($index) {
                return [$this->rowRuleFieldPrefix($index).'.'.$handle => $attribute];
            });
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->filter()->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->defaultRowData()->all(),
            'new' => $this->fields()->meta()->all(),
            'existing' => collect($this->field->value())->mapWithKeys(function ($row, $index) {
                return [$row['_id'] => $this->fields($index)->addValues($row)->meta()];
            })->toArray(),
        ];
    }

    protected function defaultRowData()
    {
        return $this->fields()->all()->map(function ($field) {
            return $field->fieldtype()->preProcess($field->defaultValue());
        });
    }

    public function augment($value)
    {
        return $this->performAugmentation($value, false);
    }

    public function shallowAugment($value)
    {
        return $this->performAugmentation($value, true);
    }

    private function performAugmentation($value, $shallow)
    {
        $method = $shallow ? 'shallowAugment' : 'augment';

        return collect($value)->map(function ($row, $index) use ($method) {
            $values = $this->fields($index)->addValues($row)->{$method}()->values();

            return new Values($values->merge([RowId::handle() => $row[RowId::handle()] ?? null])->all());
        })->all();
    }

    public function toGqlType()
    {
        return GraphQL::listOf(GraphQL::type($this->gqlItemTypeName()));
    }

    public function addGqlTypes()
    {
        GraphQL::addType($type = new GridItemType($this, $this->gqlItemTypeName()));

        $this->fields()->all()->each(function ($field) {
            $field->fieldtype()->addGqlTypes();
        });
    }

    private function gqlItemTypeName()
    {
        return 'GridItem_'.collect($this->field->handlePath())->map(function ($part) {
            return Str::studly($part);
        })->join('_');
    }

    public function preProcessValidatable($value)
    {
        return collect($value)->map(function ($values, $index) {
            $processed = $this->fields($index)
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
