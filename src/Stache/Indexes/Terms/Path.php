<?php

namespace Statamic\Stache\Indexes\Terms;

class Path extends Value
{
    public function getItemValue($item)
    {
        return $item->locale().'::'.$item->path();
    }
}
