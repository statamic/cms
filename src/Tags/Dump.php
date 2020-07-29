<?php

namespace Statamic\Tags;

class Dump extends Tags
{
    /**
     * {{ dump }}.
     */
    public function index()
    {
        dump($this->context->except(['__env', 'app'])->all());
    }

    /**
     * {{ dump:* }}.
     */
    public function wildcard($var)
    {
        dump($this->context->value($var));
    }
}
