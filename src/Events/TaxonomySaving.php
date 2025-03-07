<?php

namespace Statamic\Events;

class TaxonomySaving extends Event
{
    public function __construct(public $taxonomy)
    {
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
