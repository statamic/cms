<?php

namespace Statamic\Events;

class TaxonomySaved extends Saved
{
    public $taxonomy;

    public function __construct($taxonomy)
    {
        $this->taxonomy = $taxonomy;
    }

    public function commitMessage()
    {
        return __('Taxonomy saved');
    }
}
