<?php

namespace Statamic\Events;

class GlobalVariablesDeleted extends Event
{
    public function __construct(public $variables)
    {
    }
}
