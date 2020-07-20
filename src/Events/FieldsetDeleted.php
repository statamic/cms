<?php

namespace Statamic\Events;

class FieldsetDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Fieldset deleted');
    }
}
