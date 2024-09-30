<?php

namespace Statamic\Events;

class LocalizedTermSaved extends Event
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
}
