<?php

namespace Statamic\Filters;

use Statamic\API;
use Statamic\Filters\Filter;

class UserGroup extends Filter
{
    public function options()
    {
        return API\UserGroup::all()->mapWithKeys(function ($group) {
            return [$group->handle() => $group->title()];
        })->all();
    }

    public function apply($query, $value)
    {
        $query->where('group', $value);
    }

    public function visibleTo($key)
    {
        if (empty($this->options())) {
            return false;
        }

        return $key === 'users';
    }
}
