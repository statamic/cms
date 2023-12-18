<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class EntryDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $entry;

    public function __construct($entry)
    {
        $this->entry = $entry;
    }

    public function commitMessage()
    {
        return __('Entry deleted', [], config('statamic.git.locale'));
    }
}
