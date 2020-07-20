<?php

namespace Statamic\Events;

class BlueprintDeleted extends Deleted
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function commitMessage()
    {
        return __('Blueprint deleted');
    }
}
