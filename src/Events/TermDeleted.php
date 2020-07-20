<?php

namespace Statamic\Events;

class TermDeleted extends Deleted
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function commitMessage()
    {
        return __('Term deleted');
    }
}
