<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Illuminate\Support\Arr;

/**
 * Landscape, portrait, square orientation queries.
 */
class Orientation extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'select',
                'options' => [
                    'landscape' => __('Landscape'),
                    'portrait' => __('Portrait'),
                    'square' => __('Square'),
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query
            ->whereNotNull('width')
            ->whereNotNull('height')
            ->where('orientation', $values['operator'], $values['value']);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];
        $translatedValue = Arr::get($this->fieldItems(), "value.options.{$value}");

        return $field.' '.strtolower($translatedOperator).' '.$translatedValue;
    }
}
