<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RoleDeleted extends Event implements ProvidesCommitMessage
{
    public $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function commitMessage()
    {
        return __('Role deleted', [], config('statamic.git.locale'));
    }
}
