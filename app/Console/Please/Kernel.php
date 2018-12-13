<?php

namespace Statamic\Console\Please;

use Statamic\Console\Please\Application as Please;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        return new Please($this->app, $this->events, $this->app->version());
    }
}
