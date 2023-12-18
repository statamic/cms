<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class RevisionSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
