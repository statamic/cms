<?php

namespace Statamic\Events;

class AssetReplaced extends Event
{
    public $originalAsset;
    public $newAsset;

    public function __construct($originalAsset, $newAsset)
    {
        $this->originalAsset = $originalAsset;
        $this->newAsset = $newAsset;
    }
}
