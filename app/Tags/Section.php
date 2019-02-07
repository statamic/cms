<?php

namespace Statamic\Tags;

use Statamic\View\Store;
use Statamic\Tags\Tag;

class Section extends Tag
{
    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        app(Store::class)->sections()->put($name, $this->parse());
    }
}
