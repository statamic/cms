<?php

namespace Statamic\Addons\Env;

use Statamic\Extend\Tags;

class EnvTags extends Tags
{
    public function __call($method, $arguments)
    {
        $env = explode(':', $this->tag)[1];

        return env($env, $this->get(['fallback', 'default']));
    }
}
