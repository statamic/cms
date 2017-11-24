<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

abstract class DataDeleted extends Event
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    public $paths;

    /**
     * @param string $id
     * @param array  $paths
     */
    public function __construct($id, array $paths)
    {
        $this->id = $id;
        $this->paths = $paths;
    }
}
