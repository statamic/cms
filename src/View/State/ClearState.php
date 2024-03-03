<?php

namespace Statamic\View\State;

use Statamic\Facades\URL;

class ClearState
{
    public function handle()
    {
        StateManager::resetState();
        URL::clearExternalUrlCache();
    }
}
