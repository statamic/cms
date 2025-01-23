<?php

namespace Statamic\Events;

class TaxonomyCreated extends Event
{
    public function __construct(public $taxonomy)
    {
    }
}
