<?php

namespace Statamic\Tags;

class Set extends Tags
{
    public function index()
    {
        return array_merge($this->context, $this->params);
    }
}
