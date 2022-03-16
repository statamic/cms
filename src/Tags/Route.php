<?php

namespace Statamic\Tags;

class Route extends Tags
{
    public function index()
    {
        return $this->wildcard($this->params->get('name'));
    }

    public function wildcard($tag)
    {
        return route($tag, $this->params->except('name')->all());
    }
}
