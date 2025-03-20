<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalVariablesDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $variables)
    {
    }

    public function commitMessage()
    {
        return __('Global Variable deleted', [], config('statamic.git.locale'));
    }
}
