<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalSetSaved extends Event implements ProvidesCommitMessage
{
    public $globals;
    public $currentUser;

    public function __construct($globals, $currentUser = null)
    {
        $this->globals = $globals;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Global Set saved', [], config('statamic.git.locale'));
    }
}
