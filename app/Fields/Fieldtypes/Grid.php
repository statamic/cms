<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\API\Helper;
use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Validation;
use Statamic\CP\FieldtypeFactory;

class Grid extends Fieldtype
{
    protected $defaultable = false;

    protected $configFields = [
        'mode' => [
            'type' => 'select',
            'default' => 'table',
            'options' => ['table' => 'Table', 'stacked' => 'Stacked'],
        ],
        'max_rows' => ['type' => 'integer'],
        'min_rows' => ['type' => 'integer'],
        'add_row' => ['type' => 'text'],
        'fields' => ['type' => 'fields'],
    ];

    public function process($data)
    {
        return collect($data)->map(function ($row) {
            return $this->processRow($row);
        })->all();
    }

    private function processRow($row)
    {
        $row = array_except($row, '_id');

        $fields = $this->fields()->addValues($row)->process()->values();

        return array_merge($row, $fields);
    }

    public function preProcess($data)
    {
        return collect($data)->map(function ($row) {
            return $this->preProcessRow($row);
        })->all();
    }

    private function preProcessRow($row)
    {
        $fields = $this->fields()->addValues($row)->preProcess()->values();

        return array_merge($row, $fields);
    }

    private function fields()
    {
        return new Fields($this->config('fields'));
    }

    public function rules(): array
    {
        $rules = ['array'];

        if ($min = $this->config('min_rows')) {
            $rules[] = 'min:' . $min;
        }

        if ($max = $this->config('max_rows')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    public function extraRules(): array
    {
        $rules = (new Validation)->fields($this->fields())->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) {
            return ["{$this->field->handle()}.*.{$handle}" => $rules];
        })->all();
    }
}
