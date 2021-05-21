<?php

namespace Statamic\Events;

use Statamic\Assets\Asset;

class AssetMoved extends Event
{
    /**
     * @var Asset
     */
    public $asset;

    /**
     * @var string
     */
    public $oldPath;

    /**
     * @param Asset $asset
     * @param string $oldPath
     */
    public function __construct($asset, $oldPath)
    {
        $this->asset = $asset;
        $this->oldPath = $oldPath;
    }
}
