<?php

namespace Statamic\Events\Data;

class GlobalSetSaved extends Saved
{
    public function commitMessage()
    {
        return __('Global Set saved');
    }
}
