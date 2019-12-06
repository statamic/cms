<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Dump extends Tags
{
    public function index()
    {
        $value = $this->context->all();

        function_exists('ddd') ? ddd($value) : dd($value);
    }
}
