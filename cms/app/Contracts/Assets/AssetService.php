<?php

namespace Statamic\Contracts\Assets;

use Statamic\Contracts\Data\DataService;

interface AssetService extends DataService
{
    /**
     * Get assets from a folder
     *
     * @param string      $folder
     * @param string|null $locale
     * @return \Statamic\Assets\AssetCollection
     */
    public function getAssets($folder, $locale = null);

    /**
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     */
    public function getContainers($locale = null);

    /**
     * @param string      $uuid
     * @param string|null $locale
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function getContainer($uuid, $locale = null);
}
