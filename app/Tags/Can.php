<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Can extends Tag
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
