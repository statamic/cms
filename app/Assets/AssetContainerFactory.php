<?php

namespace Statamic\Assets;

use Statamic\API\Helper;
use Statamic\Contracts\Assets\AssetContainerFactory as FactoryContract;

class AssetContainerFactory implements FactoryContract
{
    /**
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function create($driver = 'local')
    {
        $driver = $driver ?: 'local';

        $container = app('Statamic\Contracts\Assets\AssetContainer');

        $container->driver($driver);

        return $container;
    }
}
