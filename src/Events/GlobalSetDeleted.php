<?php

namespace Statamic\Events;

class GlobalSetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Global Set deleted');
    }
}
