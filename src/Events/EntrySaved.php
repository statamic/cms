<?php

namespace Statamic\Events;

class EntrySaved extends Saved
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function commitMessage()
    {
        return __('Entry saved');
    }
}
