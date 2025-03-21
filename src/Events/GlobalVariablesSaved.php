<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalVariablesSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $variables)
    {
    }

    public function commitMessage()
    {
        return __('Global Variable saved', [], config('statamic.git.locale'));
    }
}
