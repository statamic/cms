<?php

namespace Statamic\Events;

class NavCreating extends Event
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
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
