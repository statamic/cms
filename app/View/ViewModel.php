<?php

namespace Statamic\View;

abstract class ViewModel
{
    protected $cascade;

    public function __construct(Cascade $cascade)
    {
        $this->cascade = $cascade;
    }

    abstract public function data(): array;
}
