<?php

namespace Statamic\Fieldtypes;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\GraphQL\Types\GridItemType;
use Statamic\Query\Scopes\Filters\Fields\Grid as GridFilter;
use Statamic\Support\Str;

class Grid extends Fieldtype
{
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
        $row = array_except($row, '_id');

        $fields = $this->fields()->addValues($row)->process()->values()->all();

        return array_merge($row, $fields);
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

        return array_merge($row, $fields, [
            '_id' => "row-$index",
        ]);
    }

    public function fields()
    {
        return new Fields($this->config('fields'), $this->field()->parent());
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
        $rules = $this->fields()->validator()->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return ["{$this->field->handle()}.*.{$handle}" => $rules];
        })->all();
    }

    public function preload()
    {
        return [
            'defaults' => $this->defaultRowData(),
            'new' => $this->fields()->meta(),
            'existing' => collect($this->field->value())->mapWithKeys(function ($row) {
                return [$row['_id'] => $this->fields()->addValues($row)->meta()];
            })->toArray(),
        ];
    }

    protected function defaultRowData()
    {
        return $this->fields()->all()->map->defaultValue();
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
            return $this->fields()->addValues($row)->{$method}()->values()->all();
        })->all();
    }

    public function graphQlType(): Type
    {
        return Type::listOf(GraphQL::type('GridItem_'.Str::studly($this->field->handle())));
    }

    public function addGqlTypes()
    {
        $type = new GridItemType($this);

        GraphQL::addType($type);
    }
}
