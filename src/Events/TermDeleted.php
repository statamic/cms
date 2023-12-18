<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TermDeleted extends Event implements ProvidesCommitMessage
{
    public $term;
    public $currentUser;

    public function __construct($term, $currentUser = null)
    {
        $this->term = $term;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Term deleted', [], config('statamic.git.locale'));
    }
}
