<?php

namespace Statamic\Providers;

use Illuminate\Console\Application as Artisan;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Statamic\Console\Commands;
use Statamic\Console\Commands\StaticWarm;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\ListCommand::class,
        Commands\AddonsDiscover::class,
        Commands\AssetsGeneratePresets::class,
        Commands\AssetsMeta::class,
        Commands\GlideClear::class,
        Commands\Install::class,
        Commands\MakeAction::class,
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
        Commands\SupportDetails::class,
        Commands\AuthMigration::class,
        Commands\Multisite::class,
        Commands\SiteClear::class,
        Commands\UpdatesRun::class,
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

    public function register()
    {
        if (version_compare(Application::VERSION, '8.37', '>=')) {
            $this->commands(StaticWarm::class);
        }
    }
}
