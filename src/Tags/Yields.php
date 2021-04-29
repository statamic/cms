<?php

namespace Statamic\Tags;

use Facades\Statamic\View\Cascade;

class Yields extends Tags
{
    protected static $aliases = ['yield'];

    public function __call($method, $args)
    {
        $name = explode(':', $this->tag)[1];

        if ($yield = Cascade::instance()->sections()->get($name)) {
            return $yield;
        }

        return $this->isPair ? $this->parse() : null;
    }
}
