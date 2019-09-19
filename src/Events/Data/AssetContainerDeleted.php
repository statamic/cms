<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class AssetContainerDeleted extends Event
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    public $path;

    /**
     * @param string $id
     * @param string $path
     */
    public function __construct($id, $path)
    {
        $this->id = $id;
        $this->path = $path;
    }
}
