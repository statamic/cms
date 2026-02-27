<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $nav)
    {
    }

    public function commitMessage()
    {
        return __('Navigation deleted', [], config('statamic.git.locale'));
    }
}
