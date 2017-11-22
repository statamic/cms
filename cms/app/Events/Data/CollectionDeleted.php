<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class CollectionDeleted extends Event
{
    /**
     * The collection handle to be removed from the routes.
     *
     * @var string
     */
    public $collection;

    /**
     * Create a new CollectionDeleted instance.
     *
     * @param  string  $collection
     * @return CollectionDeleted
     */
    public function __construct($collection)
    {
        $this->collection = $collection;
    }
}