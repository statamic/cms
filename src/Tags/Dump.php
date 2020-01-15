<?php

namespace Statamic\Tags;

use Statamic\Support\Arr;
use Statamic\Tags\Tags;

class Dump extends Tags
{
    public function index()
    {
        dump(Arr::except($this->context->all(), ['__env', 'app']));
    }
}
