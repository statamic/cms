<?php

namespace Statamic\Events;

class RoleDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Role deleted');
    }
}
