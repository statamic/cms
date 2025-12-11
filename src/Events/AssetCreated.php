<?php

namespace Statamic\Events;

class AssetCreated extends Event
{
    public function __construct(public $asset)
    {
    }
}
