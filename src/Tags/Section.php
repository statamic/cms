<?php

namespace Statamic\Tags;

use Facades\Statamic\View\Cascade;

class Section extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        Cascade::instance()->sections()->put($name, $this->parse());
    }
}
