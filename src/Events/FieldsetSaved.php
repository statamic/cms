<?php

namespace Statamic\Events;

class FieldsetSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Fieldset saved');
    }
}
