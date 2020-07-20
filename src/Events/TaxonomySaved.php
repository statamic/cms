<?php

namespace Statamic\Events;

class TaxonomySaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Taxonomy saved');
    }
}
