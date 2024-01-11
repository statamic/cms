<?php

namespace Statamic\Events;

class GlobalVariablesDeleting extends Event
{
    public $variables;

    public function __construct($variables)
    {
        $this->variables = $variables;
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
