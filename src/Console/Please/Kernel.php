<?php

namespace Statamic\Console\Please;

use App\Console\Kernel as ConsoleKernel;
use Statamic\Console\Please\Application as Please;
use Statamic\Statamic;

class Kernel extends ConsoleKernel
{
    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        return tap(
            new Please($this->app, $this->events, Statamic::version())
        )->setName('Statamic');
    }
}
