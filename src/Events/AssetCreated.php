<?php

namespace Statamic\Events;

class AssetCreated extends Event
{
    public $asset;

    public function __construct($asset)
    {
        $this->asset = $asset;
    }
}
