<?php

namespace Statamic\Console\Please;

use App\Console\Kernel as ConsoleKernel;
use Statamic\Console\Please\Application as Please;
use Statamic\Statamic;
use Statamic\Support\Str;
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
                (new Please($this->app, $this->events, Statamic::version()))
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

    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->getArtisan()->resolveDeferredCommands();

        if (Str::startsWith($command, 'statamic:')) {
            $command = Str::after($command, 'statamic:');
        }

        return parent::call($command, $parameters, $outputBuffer);
    }

    protected function shouldDiscoverCommands()
    {
        return get_class($this) === __CLASS__;
    }
}
