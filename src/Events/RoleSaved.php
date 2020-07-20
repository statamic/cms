<?php

namespace Statamic\Events;

class RoleSaved extends Saved
{
    public function commitMessage()
    {
        return __('Role saved');
    }
}
