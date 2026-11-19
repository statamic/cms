<?php

namespace Statamic\Tags;

use Statamic\Support\Traits\ChecksDumpability;

class Dd extends Tags
{
    use ChecksDumpability;

    protected static $aliases = ['ddd'];

    public function index()
    {
        if (! $this->dumpingAllowed()) {
            return;
        }

        $value = $this->context->sortKeys()->all();

        function_exists('ddd') ? ddd($value) : dd($value);
    }
}
