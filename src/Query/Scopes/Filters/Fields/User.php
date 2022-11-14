<?php

namespace Statamic\Query\Scopes\Filters\Fields;

use Statamic\Facades\User as Users;

class User extends FieldtypeFilter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'type' => 'users',
                'max_items' => 1,
                'mode' => 'select',
            ],
        ];
    }

    public function apply($query, $handle, $values)
    {
        if (! $user = $values['value']) {
            return;
        }

        $this->fieldtype->config('max_items') === 1
            ? $query->where($handle, $user)
            : $query->whereJsonContains($handle, $user);
    }

    public function badge($values)
    {
        if (! $user = $values['value']) {
            return null;
        }

        $field = $this->fieldtype->field()->display();
        $operator = __('Is');
        $user = Users::find($user)->name();

        return $field.' '.strtolower($operator).' '.$user;
    }
}
