<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class In extends Tag
{
    /**
     * Maps to {{ in:[group] }}
     *
     * @param  string $method
     * @param  array $args
     * @return string
     */
    public function __call($method, $args)
    {
        if ($this->api()->in($method)) {
            return $this->parse([]);
        }
    }
}
