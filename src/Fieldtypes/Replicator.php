<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fields;
use Statamic\Fields\Fieldtype;
use Statamic\CP\FieldtypeFactory;

class Replicator extends Fieldtype
{
    protected $defaultValue = [];
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

        $fields = $this->fields($row['type'])->addValues($row)->process()->values()->all();

        return array_merge($row, $fields);
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

        return array_merge($row, $fields, [
            '_id' => "set-$index",
            'enabled' => $row['enabled'] ?? true,
        ]);
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
        $rules = $this->fields($handle)->addValues($data)->validator()->rules();

        return collect($rules)->mapWithKeys(function ($rules, $handle) use ($index) {
            return [$this->setRuleFieldKey($handle, $index) => $rules];
        })->all();
    }

    protected function setRuleFieldKey($handle, $index)
    {
        return "{$this->field->handle()}.{$index}.{$handle}";
    }

    protected function setConfig($handle)
    {
        return array_get($this->getFieldConfig('sets'), $handle);
    }

    public function augment($array)
    {
        return collect($array)->reject(function ($set, $key) {
            return array_get($set, 'enabled', true) === false;
        })->map(function ($set) {
            if (! $config = $this->config("sets.{$set['type']}.fields")) {
                return $set;
            }

            $values = (new Fields($config))->addValues($set)->augment()->values();

            return array_merge($values, ['type' => $set['type']]);
        });
    }

    public function preload()
    {
        return [
            'existing' => collect($this->field->value())->mapWithKeys(function ($set) {
                $config = $this->config("sets.{$set['type']}.fields", []);
                return [$set['_id'] => (new Fields($config))->addValues($set)->meta()];
            })->toArray(),
            'new' => collect($this->config('sets'))->map(function ($set, $handle) {
                return (new Fields($set['fields']))->meta();
            })->toArray(),
            'defaults' => collect($this->config('sets'))->map(function ($set) {
                return (new Fields($set['fields']))->all()->map->defaultValue();
            })->all(),
            'collapsed' => [],
        ];
    }
}
