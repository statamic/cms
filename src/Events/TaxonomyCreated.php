<?php

namespace Statamic\Events;

class TaxonomyCreated extends Event
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }
}
