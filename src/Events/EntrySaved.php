<?php

namespace Statamic\Events;

use Facades\Statamic\Entries\InitiatorStack;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class EntrySaved extends Event implements ProvidesCommitMessage
{
    public $initiator;

    public function __construct(public $entry)
    {
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
