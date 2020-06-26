<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class BlueprintFoundOnFile extends Event
{
    public $blueprint;
    public $type;
    public $data;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }
}
