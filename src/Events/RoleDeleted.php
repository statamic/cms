<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RoleDeleted extends Event implements ProvidesCommitMessage
{
    public $role;
    public $currentUser;

    public function __construct($role, $currentUser = null)
    {
        $this->role = $role;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Role deleted', [], config('statamic.git.locale'));
    }
}
