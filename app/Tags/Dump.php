<?php

namespace Statamic\Tags;

use Statamic\Tags\Tag;

class Dump extends Tag
{
    public function index()
    {
        dd($this->context);
    }
}
