<?php

namespace Statamic\Stache\Indexes\Users;

use Statamic\Stache\Indexes\Value;
use Statamic\Support\Str;

class Role extends Value
{
    public function getItemValue($item)
    {
        $role = Str::after($this->name(), 'roles/');

        return $item->hasRole($role);
    }
}
