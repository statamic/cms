<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class RoleDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $role)
    {
    }

    public function commitMessage()
    {
        return __('Role deleted', [], config('statamic.git.locale'));
    }
}
