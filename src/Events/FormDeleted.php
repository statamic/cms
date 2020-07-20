<?php

namespace Statamic\Events;

class FormDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Form deleted');
    }
}
