<?php

namespace Statamic\Events\Data;

class RoleSaved extends Saved
{
    public function commitMessage()
    {
        return __('Role saved');
    }
}
