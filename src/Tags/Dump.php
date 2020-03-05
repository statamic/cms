<?php

namespace Statamic\Tags;

use Statamic\Tags\Tags;

class Dump extends Tags
{
    /**
     * {{ dump }}
     */
    public function index()
    {
        dump($this->context->except(['__env', 'app'])->all());
    }

    /**
     * {{ dump:* }}
     */
    public function wildcard($var)
    {
        dump($this->context->get($var));
    }
}
