<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Extend\HasFields;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FieldtypeFilter
{
    use HasFields;

    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    'like' => __('Contains'),
                    '=' => __('Is'),
                    '<>' => __('Isn\'t'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => 'like',
            ],
            'value' => [
                'type' => 'text',
                'placeholder' => __('Value'),
                'if' => [
                    'operator' => 'contains_any like, =, <>',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];
        $value = $values['value'];

        if ($operator === 'like') {
            $value = Str::ensureLeft($value, '%');
            $value = Str::ensureRight($value, '%');
        }

        match ($operator) {
            'null' => $query->whereNull($handle),
            'not-null' => $query->whereNotNull($handle),
            default => $query->where($handle, $operator, $value),
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
