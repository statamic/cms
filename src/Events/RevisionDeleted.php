<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RevisionDeleted extends Event implements ProvidesCommitMessage
{
    public $revision;

    public function __construct($revision)
    {
        $this->revision = $revision;
    }

    public function commitMessage()
    {
        return __('Revision deleted', [], config('statamic.git.locale'));
    }
}
