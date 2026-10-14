<?php

namespace Statamic\Listeners;

use Statamic\CP\Breadcrumbs\Breadcrumbs;
use Statamic\Facades\URL;
use Statamic\Statamic;
use Statamic\View\State\StateManager;

class ClearState
{
    public function handle()
    {
        StateManager::resetState();
        URL::clearUrlCache();
        Statamic::restoreJsonVariablesSnapshot();
        Statamic::$isRenderingCpException = false;
        Breadcrumbs::$pushed = [];
    }
}
