<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Support\Arr;

use function Statamic\trans as __;

class Toggle extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'select',
                'placeholder' => __('Select Value'),
                'options' => [
                    'true' => __('True'),
                    'false' => __('False'),
                ],
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $value = $values['value'];

        $query->where($handle, '=', $value === 'true');
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = __('Is');
        $value = $values['value'];

        return $field.' '.strtolower($operator).' '.$value;
    }

    public function isComplete($values): bool
    {
        $values = Arr::removeNullValues($values);

        return Arr::has($values, 'value');
    }
}
