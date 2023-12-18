<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class EntryDeleted extends Event implements ProvidesCommitMessage
{
    public $entry;
    public $currentUser;

    public function __construct($entry, $currentUser = null)
    {
        $this->entry = $entry;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Entry deleted', [], config('statamic.git.locale'));
    }
}
