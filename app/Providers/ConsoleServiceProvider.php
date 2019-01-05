<?php

namespace Statamic\Providers;

use Statamic\Console\Commands;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Application as Artisan;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\ListCommand::class,
        Commands\AddonsDiscover::class,
        Commands\GlideClear::class,
        Commands\Install::class,
        Commands\MakeFieldtype::class,
        Commands\MakeFilter::class,
        Commands\MakeModifier::class,
        Commands\MakeTag::class,
        Commands\MakeWidget::class,
        Commands\MakeBiscuit::class,
        Commands\StacheClear::class,
        Commands\StacheRefresh::class,
        Commands\StaticClear::class,
        // Commands\MakeUserMigration::class,
        // Commands\SiteClear::class,
    ];

    public function boot()
    {
        Artisan::starting(function ($artisan) {
            $artisan->resolveCommands($this->commands);
        });

        $this->publishes([
            __DIR__.'/../Console/Please/please.stub' => base_path('please'),
        ], 'statamic');
    }
}
