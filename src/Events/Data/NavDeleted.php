<?php

namespace Statamic\Events\Data;

class NavDeleted extends Deleted
{
    public function commitMessage()
    {
        return __('Navigation deleted');
    }
}
