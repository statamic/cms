<?php

namespace Statamic\Console\Please;

use App\Console\Kernel as ConsoleKernel;
use Statamic\Console\Please\Application as Please;
use Statamic\Statamic;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Kernel extends ConsoleKernel
{
    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            $this->artisan = tap(
                (new Please($this->app, $this->events, $this->app->version()))
                    ->resolveCommands($this->commands)
                    ->setContainerCommandLoader()
            )->setName('Statamic');

            if ($this->symfonyDispatcher instanceof EventDispatcher) {
                $this->artisan->setDispatcher($this->symfonyDispatcher);
                $this->artisan->setSignalsToDispatchEvent();
            }
        }

        return $this->artisan;
    }

    protected function shouldDiscoverCommands()
    {
        return get_class($this) === __CLASS__;
    }
}
