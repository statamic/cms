<?php

namespace Statamic\Events;

class TermDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Term deleted');
    }
}
