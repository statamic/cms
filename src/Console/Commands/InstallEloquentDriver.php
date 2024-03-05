<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Support\Str;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\warning;
use function Laravel\Prompts\info;

class InstallEloquentDriver extends Command
{
    use EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install:eloquent-driver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Install & configure Statamic's Eloquent Driver package";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! Composer::isInstalled('statamic/eloquent-driver')) {
            $this->info('Installing the statamic/eloquent-driver package...');
            Composer::withoutQueue()->throwOnFailure()->require('statamic/eloquent-driver');
            $this->checkLine('Installed statamic/eloquent-driver package');
        }

        if (! File::exists(config_path('statamic/eloquent-driver.php'))) {
            $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-config');

            // By default, all repositories are set `eloquent`. We'll change them back to `file` since we'll switch them one by one
            $configContents = File::get(config_path('statamic/eloquent-driver.php'));
            $configContents = Str::of($configContents)
                ->replace("=> 'eloquent'", "=> 'file'")
                ->__toString();
            File::put(config_path('statamic/eloquent-driver.php'), $configContents);

            $this->checkLine('Config file published. You can find it at config/statamic/eloquent-driver.php');
        }

        $repositories = multiselect(
            label: 'Which repositories would you like to migrate?',
            options: [
                'assets' => 'Assets',
                'blueprints' => 'Blueprints & Fieldsets',
                'collections' => 'Collections',
                'entries' => 'Entries',
                'forms' => 'Forms',
                'globals' => 'Globals',
                'navs' => 'Navs',
                'revisions' => 'Revisions',
                'taxonomies' => 'Taxonomies',
            ],
            default: ['assets', 'blueprints', 'collections', 'entries', 'forms', 'globals', 'navs', 'revisions', 'taxonomies'],
            hint: 'You can always import other repositories later.'
        );

        foreach ($repositories as $repository) {
            $method = 'migrate'.Str::studly($repository);
            $this->$method();
        }
    }

    protected function migrateAssets(): void
    {
        if (
            config('statamic.eloquent-driver.asset_containers.driver') === 'eloquent'
            || config('statamic.eloquent-driver.assets.driver') === 'eloquent'
        ) {
            warning('Assets have already been migrated. Skipping...');
            return;
        }

        info('Migrating assets...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-asset-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'asset_containers' => [\n        'driver' => 'file'", "'asset_containers' => [\n        'driver' => 'eloquent'")
            ->replace("'assets' => [\n        'driver' => 'file'", "'assets' => [\n        'driver' => 'eloquent'")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured assets');

        if (confirm('Would you like to import existing assets?')) {
            $this->runArtisanCommand('statamic:eloquent:import-assets --force');
            $this->checkLine("Imported existing assets");
        }
    }

    protected function migrateBlueprints(): void
    {
        if (config('statamic.eloquent-driver.blueprints.driver') === 'eloquent') {
            warning('Blueprints have already been migrated. Skipping...');
            return;
        }

        info('Migrating blueprints...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-blueprint-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'blueprints' => [\n        'driver'          => 'file',", "'blueprints' => [\n        'driver'          => 'eloquent',")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured blueprints');

        if (confirm('Would you like to import existing blueprints?')) {
            $this->runArtisanCommand('statamic:eloquent:import-blueprints');
            $this->checkLine("Imported existing blueprints");
        }
    }

    protected function migrateCollections(): void
    {
        if (
            config('statamic.eloquent-driver.collections.driver') === 'eloquent'
            || config('statamic.eloquent-driver.collection_trees.driver') === 'eloquent'
        ) {
            warning('Collections have already been migrated. Skipping...');
            return;
        }

        info('Migrating collections...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-collection-migrations');
        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'collections' => [\n        'driver' => 'file'", "'collections' => [\n        'driver' => 'eloquent'")
            ->replace("'collection_trees' => [\n        'driver' => 'file'", "'collection_trees' => [\n        'driver' => 'eloquent'")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured collections');

        if (confirm('Would you like to import existing collections?')) {
            $this->runArtisanCommand('statamic:eloquent:import-collections --force');
            $this->checkLine("Imported existing collections");
        }
    }

    protected function migrateEntries(): void
    {
        //
    }

    protected function migrateForms(): void
    {
        if (config('statamic.eloquent-driver.forms.driver') === 'eloquent') {
            warning('Forms have already been migrated. Skipping...');
            return;
        }

        info('Migrating forms...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-form-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'forms' => [\n        'driver'           => 'file'", "'forms' => [\n        'driver'           => 'eloquent'")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured forms');

        if (confirm('Would you like to import existing forms?')) {
            $this->runArtisanCommand('statamic:eloquent:import-forms');
            $this->checkLine("Imported existing forms");
        }
    }

    protected function migrateGlobals(): void
    {
        if (
            config('statamic.eloquent-driver.global_sets.driver') === 'eloquent'
            || config('statamic.eloquent-driver.global_set_variables.driver') === 'eloquent'
        ) {
            warning('Globals have already been migrated. Skipping...');
            return;
        }

        info('Migrating globals...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-global-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'global_sets' => [\n        'driver' => 'file'", "'global_sets' => [\n        'driver' => 'eloquent'")
            ->replace("'global_set_variables' => [\n        'driver' => 'file'", "'global_set_variables' => [\n        'driver' => 'eloquent'")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured globals');

        if (confirm('Would you like to import existing globals?')) {
            $this->runArtisanCommand('statamic:eloquent:import-globals');
            $this->checkLine("Imported existing globals");
        }
    }

    protected function migrateNavs(): void
    {
        if (
            config('statamic.eloquent-driver.navigations.driver') === 'eloquent'
            || config('statamic.eloquent-driver.navigation_trees.driver') === 'eloquent'
        ) {
            warning('Navs have already been migrated. Skipping...');
            return;
        }

        info('Migrating navs...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-migrations');

        $configContents = File::get(config_path('statamic/eloquent-driver.php'));
        $configContents = Str::of($configContents)
            ->replace("'navigations' => [\n        'driver'     => 'file'", "'navigations' => [\n        'driver'     => 'eloquent'")
            ->replace("'navigation_trees' => [\n        'driver' => 'file'", "'navigation_trees' => [\n        'driver' => 'eloquent'")
            ->__toString();
        File::put(config_path('statamic/eloquent-driver.php'), $configContents);

        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured navs');

        if (confirm('Would you like to import existing navs?')) {
            $this->runArtisanCommand('statamic:eloquent:import-navs --force');
            $this->checkLine("Imported existing navs");
        }
    }

    protected function migrateRevisions(): void
    {
        //
    }

    protected function migrateTaxonomies(): void
    {
        //
    }

    private function runArtisanCommand(string $command, bool $writeOutput = false): ProcessResult
    {
        $components = array_merge(
            [
                (new PhpExecutableFinder())->find(false) ?: 'php',
                defined('ARTISAN_BINARY') ? ARTISAN_BINARY : 'artisan',
            ],
            explode(' ', $command)
        );

        $result = Process::run($components, function ($type, $line) use ($writeOutput) {
            if ($writeOutput) {
                $this->output->write($line);
            }
        });

        // We're doing this instead of ->throw() so we can control the output of errors.
        if ($result->failed()) {
            error('Failed to run command: '.$command);
            $this->output->write($result->output());
            exit(1);
        }

        return $result;
    }
}
