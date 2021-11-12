<?php

namespace Statamic\Stache\Indexes;

class parent extends Value
{
    public function getItemValue($item)
    {
        return optional($item->parent())->id();
    }
}
