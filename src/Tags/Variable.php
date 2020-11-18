<?php

namespace Statamic\Tags;

use Statamic\Facades\Blink;

class Variable extends Tags
{
    /**
     * The {{ variable }} tag.
     *
     * @return string
     */
    public function wildcard($key)
    {
        if ($value = $this->params->get('value')) {
            Blink::put($key, $value);

            return;
        }

        return Blink::get($key, $this->params->get('default'));
    }
}
