<?php

namespace Statamic\Events;

class TermSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Term saved');
    }
}
