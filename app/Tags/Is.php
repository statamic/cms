<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Is extends Tags
{
    /**
     * Maps to {{ is:[role] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->is($method)) {
            return $this->parse([]);
        }
    }
}
