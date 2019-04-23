<?php

namespace Statamic\Tags;

use Statamic\View\Cascade;

class Scope extends Tags
{
    public function __call($method, $args)
    {
        app(Cascade::class)->set($this->method, $this->context);

        return $this->context;
    }
}
