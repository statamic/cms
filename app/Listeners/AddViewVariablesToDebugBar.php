<?php

namespace Statamic\Listeners;

use Statamic\Events\ViewRendered;
use DebugBar\DataCollector\ConfigCollector;

class AddViewVariablesToDebugbar
{
    /**
     * Handle the event.
     *
     * @param  ViewRendered  $event
     * @return void
     */
    public function handle(ViewRendered $event)
    {
        if (! app()->bound('debugbar')) {
            return;
        }

        $variables = $event->view->data();

        ksort($variables);

        debugbar()->addCollector(new ConfigCollector($variables, 'Variables'));
    }
}
