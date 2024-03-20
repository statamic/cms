<?php

namespace Statamic\Events;

class CollectionSaving extends Event
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
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
