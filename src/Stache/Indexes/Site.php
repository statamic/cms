<?php

namespace Statamic\Stache\Indexes;

class Site extends Value
{
    public function getItemValue($item)
    {
        return $item->locale();
    }
}
