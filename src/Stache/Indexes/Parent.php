<?php

namespace Statamic\Stache\Indexes;

class Parent extends Value
{
    public function getItemValue($item)
    {
        return optional($item->parent())->id();
    }
}
