<?php

namespace Statamic\View\Debugbar;

use Statamic\View\Events\ViewRendered;

class AddVariables
{
    /**
     * Handle the event.
     *
     * @param  ViewRendered  $event
     * @return void
     */
    public function handle(ViewRendered $event)
    {
        if (! debugbar()->isEnabled()) {
            return;
        }

        $variables = $event->view->gatherData();

        ksort($variables);

        debugbar()->addCollector(new VariableCollector($variables, 'Variables'));
    }
}
