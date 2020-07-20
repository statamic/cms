<?php

namespace Statamic\Events;

class UserGroupSaved extends Saved
{
    public function commitMessage()
    {
        return __('User group saved');
    }
}
