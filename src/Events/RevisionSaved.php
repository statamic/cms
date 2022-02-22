<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RevisionSaved extends Event implements ProvidesCommitMessage
{
    public $revision;

    public function __construct($revision)
    {
        $this->revision = $revision;
    }

    public function commitMessage()
    {
        return __('Revision saved', [], config('statamic.git.locale'));
    }
}
