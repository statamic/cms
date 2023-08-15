<?php

namespace Statamic\Events;

class FieldsetCreated extends Event
{
    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
    }
}
