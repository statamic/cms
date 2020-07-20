<?php

namespace Statamic\Events;

class FormSubmitted extends Saving
{
    //
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }
}
