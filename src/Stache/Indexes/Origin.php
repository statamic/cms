<?php

namespace Statamic\Stache\Indexes;

class Origin extends Value
{
    public function getItemValue($item)
    {
        if (! $item->origin()) {
            return null;
        }

        return $item->origin()->id();
    }
}
