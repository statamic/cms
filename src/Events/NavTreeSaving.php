<?php

namespace Statamic\Events;

class NavTreeSaving extends Event
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
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
