<?php

namespace Statamic\Events;

/**
 * @phpstan-consistent-constructor
 */
class AssetContainerCreating extends Event
{
    public function __construct(public $container)
    {
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
