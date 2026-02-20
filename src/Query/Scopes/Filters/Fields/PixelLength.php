<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;

class PixelLength extends Integer
{
    public function fieldItems()
    {
        $fields = parent::fieldItems();

        return [
            ...$fields,
            'value' => [
                ...$fields['value'],
                'append' => 'px',
            ],
        ];
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = number_format($values['value'], 0).' px';

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
