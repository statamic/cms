<?php

namespace Statamic\Tags;

class Route extends Tags
{
    public function wildcard($tag)
    {
        return route($tag, $this->params->except('name')->all());
    }
}
