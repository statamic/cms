<?php

namespace Statamic\Events;

class FieldsetSaving extends Event
{
    public $fieldset;

    public function __construct($fieldset)
    {
        $this->fieldset = $fieldset;
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
