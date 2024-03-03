<?php

namespace Statamic\View\State;

use Statamic\Statamic;
use Statamic\Facades\URL;

class ClearState
{
    public function handle()
    {
        Statamic::clearApiRouteCache();
        StateManager::resetState();
        URL::clearExternalUrlCache();
    }
}
