<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Dd extends Tags
{
    protected static $aliases = ['ddd'];

    public function index()
    {
        $value = $this->context->all();

        function_exists('ddd') ? ddd($value) : dd($value);
    }
}
