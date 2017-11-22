<?php

namespace Statamic\Events\Data;

use Statamic\Contracts\Assets\AssetContainer;

class AssetContainerSaved
{
    /**
     * @var AssetContainer
     */
    public $container;

    /**
     * @param AssetContainer $container
     */
    public function __construct(AssetContainer $container)
    {
        $this->container = $container;
    }
}
