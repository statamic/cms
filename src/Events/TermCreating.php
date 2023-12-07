<?php

namespace Statamic\Events;

class TermCreating extends Event
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
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
