<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Can extends Tags
{
    /**
     * Maps to {{ can:[permission] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->can($method)) {
            return $this->parse([]);
        }
    }
}
