<?php

namespace Statamic\Events;

class GlobalVariablesCreated extends Event
{
    public function __construct(public $variables)
    {
    }
}
