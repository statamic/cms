<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TermSaved extends Event implements ProvidesCommitMessage
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function commitMessage()
    {
        return __('Term saved', [], config('statamic.git.locale'));
    }
}
