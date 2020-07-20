<?php

namespace Statamic\Events;

class EntryDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Entry deleted');
    }
}
