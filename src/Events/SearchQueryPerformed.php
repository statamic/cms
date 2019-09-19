<?php

namespace Statamic\Events;

class SearchQueryPerformed extends Event
{
    /**
     * @var string
     */
    public $query;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($query)
    {
        $this->query = $query;
    }
}
