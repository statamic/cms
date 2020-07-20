<?php

namespace Statamic\Events;

class NavSaved extends Saved
{
    public function commitMessage()
    {
        return __('Navigation saved');
    }
}
