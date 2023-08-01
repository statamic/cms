<?php

namespace Statamic\Events;

class GlobalVariablesDeleted extends Event
{
    public $variable;

    public function __construct($variable)
    {
        $this->variable = $variable;
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
