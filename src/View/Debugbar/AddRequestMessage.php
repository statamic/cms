<?php

namespace Statamic\View\Debugbar;

use Statamic\Routing\Route;
use Statamic\View\Events\ViewRendered;

class AddRequestMessage
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ViewRendered $event)
    {
        if (! debugbar()->isEnabled()) {
            return;
        }

        if (! $item = $event->view->cascadeContent()) {
            return;
        }

        $message = "{$this->label($item)} loaded by URL Request";

        debugbar()->addMessage($message, 'statamic');
    }

    protected function label($item)
    {
        if ($item instanceof Route) {
            return 'Route '.$item->url();
        }

        return class_basename($item).' '.$item->id();
    }
}
