<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RoleSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $role)
    {
    }

    public function commitMessage()
    {
        return __('Role saved', [], config('statamic.git.locale'));
    }
}
