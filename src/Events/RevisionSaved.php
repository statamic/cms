<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RevisionSaved extends Event implements ProvidesCommitMessage
{
    public $revision;
    public $currentUser;

    public function __construct($revision, $currentUser = null)
    {
        $this->revision = $revision;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Revision saved', [], config('statamic.git.locale'));
    }
}
