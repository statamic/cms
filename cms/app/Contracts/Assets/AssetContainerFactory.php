<?php

namespace Statamic\Contracts\Assets;

interface AssetContainerFactory
{
    /**
     * @return AssetContainer
     */
    public function create();
}
