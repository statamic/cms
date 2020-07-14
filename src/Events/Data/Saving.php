<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

abstract class Saving extends Event
{
    public $item;

    /**
     * Instantiate saving event.
     *
     * @param mixed $item
     */
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
