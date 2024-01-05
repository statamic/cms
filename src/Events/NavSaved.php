<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavSaved extends Event implements ProvidesCommitMessage
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }

    public function commitMessage()
    {
        return __('Navigation saved', [], config('statamic.git.locale'));
    }
}
