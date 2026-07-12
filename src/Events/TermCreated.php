<?php

namespace Statamic\Events;

class TermCreated extends Event
{
    public function __construct(public $term)
    {
    }
}
