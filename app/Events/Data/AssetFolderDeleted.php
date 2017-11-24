<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;
use Statamic\Contracts\Assets\AssetContainer;

class AssetFolderDeleted extends Event
{
    /**
     * @var AssetContainer
     */
    public $container;

    /**
     * @var string
     */
    public $folder_path;

    /**
     * @var array
     */
    public $paths;

    /**
     * @param string $container  The asset container
     * @param string $path       The path to the folder
     * @param array  $paths      Any paths that have been deleted. They are relative to the asset container.
     */
    public function __construct(AssetContainer $container, $path, array $paths)
    {
        $this->container = $container;
        $this->folder_path = $path;
        $this->paths = $paths;
    }
}
