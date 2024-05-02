<?php

namespace Statamic\Tags;

use Statamic\Support\Dumper;

class Dump extends Tags
{
    /**
     * {{ dump }}.
     */
    public function index()
    {
        $values = $this->context->except(['__env', 'app'])->sortKeys()->all();

        dump(Dumper::resolve($values));
    }

    /**
     * {{ dump:* }}.
     */
    public function wildcard($var)
    {
        $values = $this->context->value($var);

        dump(Dumper::resolve($values));
    }
}
