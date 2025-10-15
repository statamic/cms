<?php

namespace Statamic\Events;

class LocalizedTermDeleted extends Event
{
    public function __construct(public $term)
    {
    }
}
