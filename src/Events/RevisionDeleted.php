<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RevisionDeleted extends Event implements ProvidesCommitMessage
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
        return __('Revision deleted', [], config('statamic.git.locale'));
    }
}
