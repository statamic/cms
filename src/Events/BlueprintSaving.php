<?php

namespace Statamic\Events;

class BlueprintSaving extends Event
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
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
