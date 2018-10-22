<?php

namespace Statamic\View;

use Statamic\Cascade;

abstract class ViewModel
{
    protected $cascade;

    public function __construct(Cascade $cascade)
    {
        $this->cascade = $cascade;
    }

    abstract public function data(): array;
}
