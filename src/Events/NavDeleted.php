<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavDeleted extends Event implements ProvidesCommitMessage
{
    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }

    public function commitMessage()
    {
        return __('Navigation deleted', [], config('statamic.git.locale'));
    }
}
