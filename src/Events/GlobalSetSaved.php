<?php

namespace Statamic\Events;

class GlobalSetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Global Set saved');
    }
}
