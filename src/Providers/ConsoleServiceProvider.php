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
        Commands\AssetsMeta::class,
        Commands\GlideClear::class,
        Commands\Install::class,
        Commands\MakeAddon::class,
        Commands\MakeFieldtype::class,
        Commands\MakeModifier::class,
        Commands\MakeScope::class,
        Commands\MakeFilter::class,
        Commands\MakeTag::class,
        Commands\MakeWidget::class,
        Commands\MakeUser::class,
        Commands\Rtfm::class,
        Commands\StacheClear::class,
        Commands\StacheRefresh::class,
        Commands\StacheWarm::class,
        Commands\StacheDoctor::class,
        Commands\StaticClear::class,
        // Commands\MakeUserMigration::class,
        Commands\AuthMigration::class,
        Commands\Multisite::class,
        Commands\SiteClear::class,
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
