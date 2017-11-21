<?php

namespace Statamic\Contracts\Assets;

use Statamic\Filesystem\FileAccessor;
use Statamic\Filesystem\FolderAccessor;
use Statamic\Contracts\Data\DataFolder;

interface AssetFolder extends DataFolder
{
    /**
     * Get the container where this folder is located
     *
     * @return AssetContainer
     */
    public function container();

    /**
     * Get the container's filesystem disk instance
     *
     * @param string $type  The type of disk instance to get
     * @return FileAccessor|FolderAccessor;
     */
    public function disk($type = 'folder');

    /**
     * Get the assets in the folder
     *
     * @param bool $recursive Whether to look for assets recursively
     * @return \Statamic\Assets\AssetCollection
     */
    public function assets($recursive = false);

    /**
     * Get the parent folder
     *
     * @return null|\Statamic\Contracts\Assets\AssetFolder
     */
    public function parent();

    /**
     * Get the number of assets in the folder
     *
     * @return int
     */
    public function count();

    /**
     * Get the resolved path to the folder
     *
     * This is the "actual" path to the folder.
     * It combines the container path with the folder path.
     *
     * @return string
     */
    public function resolvedPath();
}
