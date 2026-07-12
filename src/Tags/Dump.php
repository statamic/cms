<?php

namespace Statamic\Tags;

use Statamic\Support\Dumper;
use Statamic\Support\Traits\ChecksDumpability;

class Dump extends Tags
{
    use ChecksDumpability;

    /**
     * {{ dump }}.
     */
    public function index()
    {
        if (! $this->dumpingAllowed()) {
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
        if (! $this->dumpingAllowed()) {
            return;
        }

        $values = $this->context->value($var);

        dump(Dumper::resolve($values));
    }
}
