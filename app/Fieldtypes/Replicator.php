<?php

namespace Statamic\Fieldtypes;

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

    protected function processRow($row)
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

    protected function preProcessRow($row)
    {
        $fields = $this->fields($row['type'])->addValues($row)->preProcess()->values();

        return array_merge($row, $fields);
    }

    protected function fields($set)
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

    protected function setRules($handle, $data, $index)
    {
        $rules = (new Validation)->fields($this->fields($handle))->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return ["{$this->field->handle()}.{$index}.{$handle}" => $rules];
        })->all();
    }

    protected function setConfig($handle)
    {
        return array_get($this->getFieldConfig('sets'), $handle);
    }

    public function augment($array)
    {
        return collect($array)->reject(function ($value, $key) {
            return array_get($value, 'enabled', true) === false;
        });
    }

    public function preload()
    {
        return [
            'existing' => collect($this->field->value())->map(function ($set) {
                $config = $this->config("sets.{$set['type']}.fields", []);
                return (new Fields($config))->addValues($set)->meta();
            })->toArray(),
            'new' => collect($this->config('sets'))->map(function ($set, $handle) {
                return (new Fields($set['fields']))->meta();
            })->toArray(),
            'defaults' => collect($this->config('sets'))->map(function ($set) {
                return (new Fields($set['fields']))->all()->map->defaultValue();
            })->all()
        ];
    }
}
