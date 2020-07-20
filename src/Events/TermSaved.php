<?php

namespace Statamic\Events;

class TermSaved extends Saved
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function commitMessage()
    {
        return __('Term saved');
    }
}
