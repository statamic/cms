<?php

namespace Statamic\Events;

class AssetContainerBlueprintFound extends Event
{
    public $blueprint;
    public $container;

    public function __construct($blueprint, $container = null)
    {
        $this->blueprint = $blueprint;
        $this->container = $container;
    }
}
