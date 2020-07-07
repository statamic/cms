<?php

namespace Statamic\Events\Data;

class RoleDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Role deleted');
    }
}
