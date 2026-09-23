<?php

namespace Statamic\Stache\Indexes\Terms;

class Parents extends Value
{
    public function getItemValue($item)
    {
        return optional($item->parent())->id();
    }
}
