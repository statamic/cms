<?php

namespace Statamic\Listeners;

use Statamic\Facades\URL;
use Statamic\Statamic;
use Statamic\View\State\StateManager;

class ClearState
{
    public function handle()
    {
        Statamic::clearApiRouteCache();
        StateManager::resetState();
        URL::clearExternalUrlCache();
    }
}
