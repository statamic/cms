<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavSaved extends Event implements ProvidesCommitMessage
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
        return __('Navigation saved', [], config('statamic.git.locale'));
    }
}
