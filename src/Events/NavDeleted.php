<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavDeleted extends Event implements ProvidesCommitMessage
{
    public $nav;
    public $currentUser;

    public function __construct($nav, $currentUser = null)
    {
        $this->nav = $nav;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Navigation deleted', [], config('statamic.git.locale'));
    }
}
