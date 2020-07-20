<?php

namespace Statamic\Events;

class UserSaved extends Saved
{
    public function commitMessage()
    {
        return __('User saved');
    }
}
