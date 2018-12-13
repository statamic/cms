<?php

namespace Statamic\Console\Please;

use App\Console\Kernel as ConsoleKernel;
use Statamic\Console\Please\Application as Please;

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
