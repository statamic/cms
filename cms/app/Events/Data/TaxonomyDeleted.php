<?php

namespace Statamic\Events\Data;

use Statamic\Events\Event;

class TaxonomyDeleted extends Event
{
    /**
     * The taxonomy handle to be removed from the routes.
     *
     * @var string
     */
    public $taxonomy;

    /**
     * Create a new CollectionDeleted instance.
     *
     * @param  string  $taxonomy
     */
    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }
}
