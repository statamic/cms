<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Set extends Tags
{
    public function index()
    {
        return array_merge($this->context, $this->parameters);
    }
}
