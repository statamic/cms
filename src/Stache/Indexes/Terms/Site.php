<?php

namespace Statamic\Stache\Indexes\Terms;

class Site extends Value
{
    public function getItemValue($item)
    {
        return $item->locale();
    }
}
