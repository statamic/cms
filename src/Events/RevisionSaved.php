<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RevisionSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $revision)
    {
    }

    public function commitMessage()
    {
        return __('Revision saved', [], config('statamic.git.locale'));
    }
}
