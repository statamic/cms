<?php

namespace Statamic\Stache\Indexes\Submissions;

use Statamic\Stache\Indexes\Value;

class Site extends Value
{
    public function getItemValue($item)
    {
        return $item->site()->handle();
    }
}
