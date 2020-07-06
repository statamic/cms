<?php

namespace Statamic\Events\Data;

class UserGroupSaved extends Saved
{
    public function commitMessage()
    {
        return __('User group saved');
    }
}
