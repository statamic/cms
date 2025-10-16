<?php

namespace Statamic\Events;

class LocalizedTermSaved extends Event
{
    public function __construct(public $term)
    {
    }
}
