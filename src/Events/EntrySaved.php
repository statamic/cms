<?php

namespace Statamic\Events;

class EntrySaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Entry saved');
    }
}
