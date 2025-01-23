<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $nav)
    {
    }

    public function commitMessage()
    {
        return __('Navigation saved', [], config('statamic.git.locale'));
    }
}
