<?php

namespace Statamic\Events;

class GlobalVariablesSaved extends Event
{
    public $variables;

    public function __construct($variables)
    {
        $this->variables = $variables;
    }
}
