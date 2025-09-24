<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades\User as Users;
use Statamic\Support\Arr;

class User extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'operator' => [
                'type' => 'select',
                'placeholder' => __('Select Operator'),
                'options' => [
                    '=' => __('Is'),
                    'null' => __('Empty'),
                    'not-null' => __('Not empty'),
                ],
                'default' => '=',
            ],
            'value' => [
                'type' => 'users',
                'max_items' => 1,
                'mode' => 'select',
                'if' => [
                    'operator' => 'contains_any like, =, !=',
                ],
                'required' => false,
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            match ($operator) {
                'null' => $query->whereNull($handle),
                'not-null' => $query->whereNotNull($handle),
            };

            return;
        }

        if (! $user = $values['value']) {
            return;
        }

        $this->fieldtype->config('max_items') === 1
            ? $query->where($handle, $user)
            : $query->whereJsonContains($handle, $user);
    }

    public function badge($values)
    {
        $field = $this->fieldtype->field()->display();
        $operator = $values['operator'];

        if (in_array($operator, ['null', 'not-null'])) {
            $translatedOperator = Arr::get($this->fieldItems(), "operator.options.{$operator}");

            return $field.' '.strtolower($translatedOperator);
        }

        if (! $user = $values['value']) {
            return null;
        }

        $user = Users::find($user)->name();

        return $field.' '.strtolower($operator).' '.$user;
    }
}
