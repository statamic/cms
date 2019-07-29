<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\API\Role;
use Statamic\Query\Scopes\Filter;

class UserRole extends Filter
{
    public function fieldItems()
    {
        return [
            'value' => [
                'display' => __('User Role'),
                'type' => 'select',
                'options' => $this->options()
            ]
        ];
    }

    public function apply($query, $values)
    {
        $query->where('role', $values['value']);
    }

    public function visibleTo($key)
    {
        if (empty($this->options())) {
            return false;
        }

        return $key === 'users';
    }

    protected function options()
    {
        return Role::all()->mapWithKeys(function ($role) {
            return [$role->handle() => $role->title()];
        })->prepend('Super', 'super')->all();
    }
}
