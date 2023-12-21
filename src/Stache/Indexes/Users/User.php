<?php

namespace Statamic\Stache\Indexes\Users;

use Statamic\Stache\Indexes\Value;
use Statamic\Support\Str;

class User extends Value
{
    public function getItemValue($item)
    {
        return $item->user()?->id();
    }
}
