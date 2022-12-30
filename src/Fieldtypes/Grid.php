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
    protected $categories = ['structured'];
    protected $defaultable = false;
    protected $defaultValue = [];

    protected function configFieldItems(): array
    {
        return [
            'fields' => [
                'display' => __('Fields'),
                'instructions' => __('statamic::fieldtypes.grid.config.fields'),
                'type' => 'fields',
            ],
            'mode' => [
                'display' => __('Mode'),
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
                'width' => '50',
            ],
            'min_rows' => [
                'display' => __('Minimum Rows'),
                'instructions' => __('statamic::fieldtypes.grid.config.min_rows'),
                'type' => 'integer',
                'width' => '50',
            ],
            'add_row' => [
                'display' => __('Add Row Label'),
                'instructions' => __('statamic::fieldtypes.grid.config.add_row'),
                'type' => 'text',
                'width' => '50',
            ],
            'reorderable' => [
                'display' => __('Reorderable'),
                'instructions' => __('statamic::fieldtypes.grid.config.reorderable'),
                'type' => 'toggle',
                'default' => true,
                'width' => '50',
            ],
        ];
    }

    public function filter()
    {
        return new GridFilter($this);
    }

    public function process($data)
    {
        return collect($data)->map(function ($row) {
            return $this->processRow($row);
        })->all();
    }

    private function processRow($row)
    {
        $fields = $this->fields()->addValues($row)->process()->values()->all();

        $row = array_merge(['id' => Arr::pull($row, '_id')], $row, $fields);

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
        $fields = $this->fields()->addValues($row)->preProcess()->values()->all();

        $id = Arr::pull($row, 'id') ?? RowId::generate();

        return array_merge($row, $fields, [
            '_id' => $id,
        ]);
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent(), $this->field());
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
            ->fields()
            ->addValues($data)
            ->validator()
            ->withContext([
                'prefix' => $this->field->validationContext('prefix').$this->rowRuleFieldPrefix($index).'.',
            ])
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
        $attributes = $this->fields()->validator()->attributes();

        return collect($this->field->value())->map(function ($row, $index) use ($attributes) {
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
            'existing' => collect($this->field->value())->mapWithKeys(function ($row) {
                return [$row['_id'] => $this->fields()->addValues($row)->meta()];
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

        return collect($value)->map(function ($row) use ($method) {
            $values = $this->fields()->addValues($row)->{$method}()->values();

            return new Values($values->merge(['id' => $row['id'] ?? null])->all());
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
        return collect($value)->map(function ($values) {
            $processed = $this->fields()
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
