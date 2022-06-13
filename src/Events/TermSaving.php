<?php

namespace Statamic\Events;

class TermSaving extends Event
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
}
