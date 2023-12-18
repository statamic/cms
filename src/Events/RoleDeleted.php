<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class RoleDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
