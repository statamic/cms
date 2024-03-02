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
        Dumper::dump($this->context->except(['__env', 'app'])->sortKeys()->all());
    }

    /**
     * {{ dump:* }}.
     */
    public function wildcard($var)
    {
        Dumper::dump($this->context->value($var));
    }
}
