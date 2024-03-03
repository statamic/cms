<?php

namespace Statamic\View\State;

use Statamic\Facades\URL;
use Statamic\Statamic;

class ClearState
{
    public function handle()
    {
        Statamic::clearApiRouteCache();
        StateManager::resetState();
        URL::clearExternalUrlCache();
    }
}
