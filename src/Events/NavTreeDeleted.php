<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavTreeDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $tree)
    {
    }

    public function commitMessage()
    {
        return __('Navigation tree deleted', [], config('statamic.git.locale'));
    }
}
