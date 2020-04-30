<?php

namespace Statamic\Tags;

use Statamic\Support\Str;

class Widont extends Tags
{
    public function index()
    {
        return Str::widont($this->content);
    }
}
