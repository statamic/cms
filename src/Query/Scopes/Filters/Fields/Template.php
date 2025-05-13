<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Arr;

class Template extends FieldtypeFilter
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
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'template',
                'if' => [
                    'operator' => 'contains_any <>, =',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $template = $values['value'];

        $variations = [
            str_replace('.', '/', $template),
            str_replace('/', '.', $template),
        ];

        match ($operator) {
            '=' => $query->whereIn($handle, $variations),
            '<>' => $query->whereNotIn($handle, $variations),
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
        };
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];
        $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");
        $value = $values['value'];

        return $field.' '.strtolower($translatedOperator).' '.$value;
    }
}
