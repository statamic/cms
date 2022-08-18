<?php

 namespace Statamic\Events;

 class RoleBlueprintFound extends Event
 {
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }
 }
