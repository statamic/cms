<?php

namespace Statamic\Events;

class TaxonomySaving extends Event
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
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
