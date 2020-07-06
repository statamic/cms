<?php

namespace Statamic\Events\Data;

class UserGroupDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('User group deleted');
    }
}
