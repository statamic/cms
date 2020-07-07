<?php

namespace Statamic\Events\Data;

class NavSaved extends Saved
{
    public function commitMessage()
    {
        return __('Navigation saved');
    }
}
