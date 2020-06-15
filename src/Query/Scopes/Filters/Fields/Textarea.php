<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Str;

class Textarea extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'text',
                'placeholder' => __('Contains'),
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $value = $values['value'];

        $value = Str::ensureLeft($value, '%');
        $value = Str::ensureRight($value, '%');

        $query->where($handle, 'like', $value);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = __('Contains');
        $value = $values['value'];

        return strtolower($field).' '.strtolower($operator).' '.$value;
    }
}
