<?php

namespace Statamic\Events;

class AssetContainerBlueprintFound extends Event
{
    public $blueprint;
    public $container;
    public $asset;

    public function __construct($blueprint, $container = null, $asset = null)
    {
        $this->blueprint = $blueprint;
        $this->container = $container;
        $this->asset = $asset;
    }
}
