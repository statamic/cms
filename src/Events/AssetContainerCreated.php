<?php

namespace Statamic\Events;

class AssetContainerCreated extends Event
{
    public function __construct(public $container)
    {
    }
}
