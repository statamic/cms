<?php

namespace Statamic\Events;

class AssetContainerCreated extends Event
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }
}
