<?php

namespace Statamic\Tags;

use Statamic\Support\Dumper;
use Statamic\Tags\Concerns\AllowDumping;

class Dump extends Tags
{
    use AllowDumping;

    /**
     * {{ dump }}.
     */
    public function index()
    {
        if (! $this->allowDumping()) {
            return;
        }

        $values = $this->context->except(['__env', 'app'])->sortKeys()->all();

        dump(Dumper::resolve($values));
    }

    /**
     * {{ dump:* }}.
     */
    public function wildcard($var)
    {
        if (! $this->allowDumping()) {
            return;
        }

        $values = $this->context->value($var);

        dump(Dumper::resolve($values));
    }
}
