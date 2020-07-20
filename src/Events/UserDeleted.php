<?php

namespace Statamic\Events;

class UserDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('User deleted');
    }
}
