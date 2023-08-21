<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Facades\Blink;

class EntrySaved extends Event implements ProvidesCommitMessage
{
    public $entry;
    public $initiator;

    public function __construct($entry)
    {
        $this->entry = $entry;
        $this->initiator = (Blink::get('entry-event-initiator-'.$entry->root()->id()) ?? collect())->first();
    }

    public function commitMessage()
    {
        return __('Entry saved', [], config('statamic.git.locale'));
    }
}
