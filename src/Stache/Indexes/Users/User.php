<?php

namespace Statamic\Stache\Indexes\Users;

use Statamic\Stache\Indexes\Value;

class User extends Value
{
    public function getItemValue($item)
    {
        return $item->user()?->id();
    }
}
