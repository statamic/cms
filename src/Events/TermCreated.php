<?php

namespace Statamic\Events;

class TermCreated extends Event
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
}
