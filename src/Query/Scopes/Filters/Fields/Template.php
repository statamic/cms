<?php

namespace Statamic\Query\Scopes\Filters\Fields;

class Template extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'template',
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $query->where($handle, $values['value']);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = __('Is');
        $value = $values['value'];

        return strtolower($field) . ' ' . strtolower($operator) . ' ' . $value;
    }
}
