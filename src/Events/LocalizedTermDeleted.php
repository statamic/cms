<?php

namespace Statamic\Events;

class LocalizedTermDeleted extends Event
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }
}
