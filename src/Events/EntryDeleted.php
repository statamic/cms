<?php

namespace Statamic\Events;

class EntryDeleted extends Deleted
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function commitMessage()
    {
        return __('Entry deleted');
    }
}
