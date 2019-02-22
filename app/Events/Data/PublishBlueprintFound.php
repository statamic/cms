<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class PublishBlueprintFound extends Event
{
    public $blueprint;
    public $type;
    public $data;

    public function __construct($blueprint, $type, $data = null)
    {
        $this->blueprint = $blueprint;
        $this->type = $type;
        $this->data = $data;
    }
}
