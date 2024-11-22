<?php

namespace Statamic\Events;

use Facades\Statamic\Entries\InitiatorStack;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class EntrySaved extends Event implements ProvidesCommitMessage
{
    public $entry;
    public $initiator;

    public function __construct($entry)
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
        if (! $this->initiator) {
            return false;
        }
        return $this->entry->id() === $this->initiator->id();
    }
}
