<?php

namespace Statamic\Events;

class TaxonomyDeleted extends Deleted
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function commitMessage()
    {
        return __('Taxonomy deleted');
    }
}
