<?php

namespace Statamic\Events;

class BlueprintSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Blueprint saved');
    }
}
