<?php

namespace Statamic\Providers;

use Statamic\Console\Commands;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\AddonsDiscover::class,
        Commands\GlideClear::class,
        Commands\Install::class,
        Commands\StacheClear::class,
        Commands\StaticClear::class,
        // Commands\MakeUserMigration::class,
        // Commands\SiteClear::class,
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
