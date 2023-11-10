<?php

namespace Statamic\Events;

class RevisionSaving extends Event
{
    public $revision;

    public function __construct($revision)
    {
        $this->revision = $revision;
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
