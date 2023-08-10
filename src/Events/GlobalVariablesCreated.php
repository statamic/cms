<?php

namespace Statamic\Events;

class GlobalVariablesCreated extends Event
{
    public $variables;

    public function __construct($variables)
    {
        $this->variables = $variables;
    }
}
