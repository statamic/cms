<?php

namespace Statamic\Events;

class UserGroupDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('User group deleted');
    }
}
