<?php

namespace Statamic\Events;

class GlideAssetCacheCleared extends Event
{
    public function __construct(public $asset)
    {
    }
}
