<?php

namespace Statamic\Events;

class AssetReplaced extends Event
{
    public function __construct(public $originalAsset, public $newAsset)
    {
    }
}
