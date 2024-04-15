<?php

namespace Statamic\Events;

class GlobalVariablesDeleted extends Event
{
    public $variables;

    public function __construct($variables)
    {
        $this->variables = $variables;
    }
}
