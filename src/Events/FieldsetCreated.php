<?php

namespace Statamic\Events;

class FieldsetCreated extends Event
{
    public function __construct(public $fieldset)
    {
    }
}
