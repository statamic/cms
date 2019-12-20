<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Dump extends Tags
{
    public function index()
    {
        dump($this->context->all());
    }
}
