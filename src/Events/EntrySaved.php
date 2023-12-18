<?php

namespace Statamic\Events;

use Facades\Statamic\Entries\InitiatorStack;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class EntrySaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $entry;
    public $initiator;

    public function __construct($entry, $currentUser = null)
    {
        $this->entry = $entry;
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
