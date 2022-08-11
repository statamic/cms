<?php

namespace Statamic\Events;

class AssetContainerSaving extends Event
{
    public $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch the event with the given arguments, and halt on first non-null listener response.
     *
     * @return mixed
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()), [], true);
    }
}
