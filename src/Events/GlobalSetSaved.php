<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalSetSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $globals)
    {
    }

    public function commitMessage()
    {
        return __('Global Set saved', [], config('statamic.git.locale'));
    }
}
