<?php

namespace Statamic\Filters;

use Statamic\API\Role;
use Statamic\Filters\Filter;

class UserRole extends Filter
{
    public function options()
    {
        return Role::all()->mapWithKeys(function ($role) {
            return [$role->handle() => $role->title()];
        })->prepend('Super', 'super')->all();
    }

    public function apply($query, $value)
    {
        $query->where('role', $value);
    }

    public function visibleTo($key)
    {
        if (empty($this->options())) {
            return false;
        }

        return $key === 'users';
    }
}
