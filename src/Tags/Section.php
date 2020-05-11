<?php

namespace Statamic\Tags;

use Statamic\View\Store;

class Section extends Tags
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        app(Store::class)->sections()->put($name, $this->parse());
    }
}
