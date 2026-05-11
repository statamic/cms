<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class EntryDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $entry)
    {
    }

    public function commitMessage()
    {
        return __('Entry deleted', [], config('statamic.git.locale'));
    }
}
