<?php

namespace Statamic\Events\Data;

class UserSaved extends Saved
{
    public function commitMessage()
    {
        return __('User saved');
    }
}
