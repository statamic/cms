<?php

namespace Statamic\Stache\Indexes;

class Parents extends Value
{
    public function getItemValue($item)
    {
        return optional($item->parent())->id();
    }
}
