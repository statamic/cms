<?php

namespace Statamic\Fields\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\Fields\Validation;
use Statamic\CP\FieldtypeFactory;

class Replicator extends Fieldtype
{
    protected $configFields = [
        'sets' => ['type' => 'sets'],
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

        $fields = $this->fields($row['type'])->addValues($row)->process()->values();

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
        $fields = $this->fields($row['type'])->addValues($row)->preProcess()->values();

        return array_merge($row, $fields);
    }

    private function fields($set)
    {
        return new Fields($this->config("sets.$set.fields"));
    }

    public function extraRules(): array
    {
        return collect($this->field->value())->map(function ($set, $index) {
            return $this->setRules($set['type'], $set, $index);
        })->reduce(function ($carry, $rules) {
            return $carry->merge($rules);
        }, collect())->all();
    }

    private function setRules($handle, $data, $index)
    {
        $rules = (new Validation)->fields($this->fields($handle))->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return ["{$this->field->handle()}.{$index}.{$handle}" => $rules];
        })->all();
    }

    private function setConfig($handle)
    {
        return array_get($this->getFieldConfig('sets'), $handle);
    }
}
