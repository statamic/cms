<?php

namespace Statamic\Events\Data;

class GlobalSetDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Global Set deleted');
    }
}
