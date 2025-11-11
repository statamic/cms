<?php

namespace Statamic\Tags;

use Statamic\Support\Traits\AllowDumping;

class Dd extends Tags
{
    use AllowDumping;

    protected static $aliases = ['ddd'];

    public function index()
    {
        if (! $this->allowDumping()) {
            return;
        }

        $value = $this->context->sortKeys()->all();

        function_exists('ddd') ? ddd($value) : dd($value);
    }
}
