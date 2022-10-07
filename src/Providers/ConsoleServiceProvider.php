<?php

namespace Statamic\Providers;

use Illuminate\Console\Application as Artisan;
use Illuminate\Support\ServiceProvider;
use Statamic\Console\Commands;

class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        Commands\ListCommand::class,
        Commands\AddonsDiscover::class,
        Commands\AssetsGeneratePresets::class,
        Commands\AssetsMeta::class,
        Commands\GlideClear::class,
        Commands\Install::class,
        Commands\LicenseSet::class,
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
        Commands\StarterKitExport::class,
        Commands\StarterKitInstall::class,
        Commands\StarterKitRunPostInstall::class,
        Commands\StaticClear::class,
        Commands\StaticWarm::class,
        // Commands\MakeUserMigration::class,
        Commands\SupportDetails::class,
        Commands\SupportZipBlueprint::class,
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
}
