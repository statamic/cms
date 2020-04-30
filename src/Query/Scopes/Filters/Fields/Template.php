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
        $template = $values['value'];

        $variations = [
            str_replace('.', '/', $template),
            str_replace('/', '.', $template),
        ];

        $query->whereIn($handle, $variations);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = __('Is');
        $value = $values['value'];

        return strtolower($field).' '.strtolower($operator).' '.$value;
    }
}
