<?php

namespace Statamic\Providers;

use Statamic\Console\Commands;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\DiscoverAddonPackages::class,
    ];

    public function boot()
    {
        Artisan::starting(function ($artisan) {
            foreach ($this->commands as $command) {
                $artisan->resolve($command);
            }
        });
    }
}
