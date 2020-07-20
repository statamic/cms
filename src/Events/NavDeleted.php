<?php

namespace Statamic\Events;

class NavDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Navigation deleted');
    }
}
