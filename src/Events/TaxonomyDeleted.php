<?php

namespace Statamic\Events;

class TaxonomyDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Taxonomy deleted');
    }
}
