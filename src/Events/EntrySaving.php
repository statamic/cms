<?php

namespace Statamic\Events;

class EntrySaving extends Event
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
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
