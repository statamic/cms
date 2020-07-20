<?php

namespace Statamic\Events;

class BlueprintDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Blueprint deleted');
    }
}
