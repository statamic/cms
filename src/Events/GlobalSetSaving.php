<?php

namespace Statamic\Events;

class GlobalSetSaving extends Event
{
    public $globalSet;

    public function __construct($globalSet)
    {
        $this->globalSet = $globalSet;
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
