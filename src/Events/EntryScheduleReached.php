<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class EntryScheduleReached extends Event implements ProvidesCommitMessage
{
    public function __construct(public $entry)
    {
    }

    public function commitMessage()
    {
        return __('Entry schedule reached', [], config('statamic.git.locale'));
    }
}
