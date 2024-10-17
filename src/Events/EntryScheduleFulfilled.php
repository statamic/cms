<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class EntryScheduleFulfilled extends Event implements ProvidesCommitMessage
{
    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function commitMessage()
    {
        return __('Entry schedule fulfilled', [], config('statamic.git.locale'));
    }
}
