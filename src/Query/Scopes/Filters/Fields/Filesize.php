<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;
use Statamic\Support\Str;

class Filesize extends Integer
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '>' => __('Greater than'),
                    '>=' => __('Greater than or equals'),
                    '<' => __('Less than'),
                    '<=' => __('Less than or equals'),
                ],
                'default' => '>',
            ],
            'value' => [
                'type' => $this->valueFieldtype(),
                'placeholder' => __('Size') . ' (KB)',
                'if' => [
                    'operator' => 'contains_any <>, >, >=, <, <=, =',
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query->where($handle, $values['operator'], $values['value'] * 1024);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = Str::fileSizeForHumans($values['value'] * 1024, 0);

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
