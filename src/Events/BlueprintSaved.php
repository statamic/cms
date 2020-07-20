<?php

namespace Statamic\Events;

class BlueprintSaved extends Saved
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function commitMessage()
    {
        return __('Blueprint saved');
    }
}
