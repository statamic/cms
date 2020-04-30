<?php

namespace Statamic\Stache\Indexes\Users;

use Statamic\Stache\Indexes\Value;
use Statamic\Support\Str;

class Group extends Value
{
    public function getItemValue($item)
    {
        $group = Str::after($this->name(), 'groups/');

        return $item->isInGroup($group);
    }
}
