<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Env extends Tag
{
    public function __call($method, $arguments)
    {
        $env = explode(':', $this->tag)[1];

        return env($env, $this->get(['fallback', 'default']));
    }
}
