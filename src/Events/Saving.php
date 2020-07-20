<?php

namespace Statamic\Events;

abstract class Saving extends Event
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
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
