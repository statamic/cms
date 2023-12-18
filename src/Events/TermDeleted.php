<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TermDeleted extends Event implements ProvidesCommitMessage
{
    public $term;

    public function __construct($term)
    {
        $this->term = $term;
    }

    public function commitMessage()
    {
        return __('Term deleted', [], config('statamic.git.locale'));
    }
}
