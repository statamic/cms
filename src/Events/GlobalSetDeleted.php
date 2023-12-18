<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalSetDeleted extends Event implements ProvidesCommitMessage
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
        return __('Global Set deleted', [], config('statamic.git.locale'));
    }
}
