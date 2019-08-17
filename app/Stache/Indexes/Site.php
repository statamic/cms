<?php

namespace Statamic\Stache\Indexes;

class Site extends Index
{
    public function getItemValue($item)
    {
        return $item->locale();
    }
}