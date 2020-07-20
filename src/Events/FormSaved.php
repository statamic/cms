<?php

namespace Statamic\Events;

class FormSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Form saved');
    }
}
