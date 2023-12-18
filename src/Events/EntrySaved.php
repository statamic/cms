<?php

namespace Statamic\Events;

use Facades\Statamic\Entries\InitiatorStack;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class EntrySaved extends Event implements ProvidesCommitMessage
{
    public $entry;
    public $currentUser;
    public $initiator;

    public function __construct($entry, $currentUser = null)
    {
        $this->entry = $entry;
        $this->currentUser = $currentUser;
        $this->initiator = InitiatorStack::entry($entry)->initiator();
    }

    public function commitMessage()
    {
        return __('Entry saved', [], config('statamic.git.locale'));
    }

    public function isInitial()
    {
        return $this->entry->id() === $this->initiator->id();
    }
}
