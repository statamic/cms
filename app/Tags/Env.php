<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Env extends Tags
{
    public function __call($method, $arguments)
    {
        $env = explode(':', $this->tag)[1];

        return env($env, $this->get(['fallback', 'default']));
    }
}
