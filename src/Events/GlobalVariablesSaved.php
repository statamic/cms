<?php

namespace Statamic\Events;

class GlobalVariablesSaved extends Event
{
    public function __construct(public $variables)
    {
    }
}
