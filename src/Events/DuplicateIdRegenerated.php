<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class DuplicateIdRegenerated extends Event implements ProvidesCommitMessage
{
    public $currentUser;

    public function __construct($currentUser)
    {
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Duplicate ID Regenerated', [], config('statamic.git.locale'));
    }
}
