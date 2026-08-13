<?php

namespace Statamic\Events;

class TaxonomyTreeSaving extends Event
{
    public function __construct(public $tree)
    {
    }
}
