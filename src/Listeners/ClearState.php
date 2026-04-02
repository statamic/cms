<?php

namespace Statamic\Listeners;

use Statamic\Facades\URL;
use Statamic\View\State\StateManager;

class ClearState
{
    public function handle()
    {
        StateManager::resetState();
        URL::clearUrlCache();
    }
}
