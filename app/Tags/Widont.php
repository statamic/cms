<?php

namespace Statamic\Tags;

use Statamic\API\Str;
use Statamic\Tags\Tags;

class Widont extends Tags
{
    public function index()
    {
        return Str::widont($this->content);
    }
}
