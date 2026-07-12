<?php

namespace Statamic\Events;

class AssetContainerBlueprintFound extends Event
{
    public function __construct(public $blueprint, public $container = null, public $asset = null)
    {
    }
}
