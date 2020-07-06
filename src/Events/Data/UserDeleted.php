<?php

namespace Statamic\Events\Data;

class UserDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('User deleted');
    }
}
