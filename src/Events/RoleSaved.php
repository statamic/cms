<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class RoleSaved extends Event implements ProvidesCommitMessage
{
    public $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function commitMessage()
    {
        return __('Role saved', [], config('statamic.git.locale'));
    }
}
