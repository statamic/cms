<?php

namespace Statamic\Contracts\Assets;

use Statamic\Assets\AssetCollection;
use Statamic\Filesystem\FlysystemAdapter;

interface AssetFolder
{
    /**
     * Get the container where this folder is located.
     *
     * @return AssetContainer
     */
    public function container();

    /**
     * Get the container's filesystem disk instance.
     *
     * @return FlysystemAdapter
     */
    public function disk();

    /**
     * Get the assets in the folder.
     *
     * @param  bool  $recursive  Whether to look for assets recursively
     * @return AssetCollection
     */
    public function assets($recursive = false);

    /**
     * Get the parent folder.
     *
     * @return null|AssetFolder
     */
    public function parent();

    /**
     * Get the number of assets in the folder.
     *
     * @return int
     */
    public function count();

    /**
     * Get the resolved path to the folder.
     *
     * @return string
     */
    public function resolvedPath();
}
