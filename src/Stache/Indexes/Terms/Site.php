<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Stache\Indexes\Terms\Value;

class Site extends Value
{
    public function getItemValue($item)
    {
        return $item->locale();
    }
}