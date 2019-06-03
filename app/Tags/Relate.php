<?php

namespace Statamic\Tags;

class Relate extends Tags
{
    public function __call($method, $args)
    {
        return $this->getContext($this->method);
    }
}
