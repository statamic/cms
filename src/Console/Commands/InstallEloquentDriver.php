<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Support\Str;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\warning;

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
            File::put(
                config_path('statamic/eloquent-driver.php'),
                Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                    ->replace("=> 'eloquent'", "=> 'file'")
                    ->__toString()
            );

            $this->checkLine('Config file published. You can find it at config/statamic/eloquent-driver.php');
        }

        if ($this->availableRepositories()->isEmpty()) {
            return warning("No repositories left to migrate. You're already using the Eloquent Driver for all repositories.");
        }

        $repositories = multiselect(
            label: 'Which repositories would you like to migrate?',
            hint: 'You can always import other repositories later.',
            options: $this->availableRepositories()->all(),
            validate: fn (array $values) => count($values) === 0
                ? 'You must select at least one repository to migrate.'
                : null
        );

        foreach ($repositories as $repository) {
            $method = 'migrate'.Str::studly($repository);
            $this->$method();
        }
    }

    protected function availableRepositories(): Collection
    {
        return collect([
            'assets' => 'Assets',
            'blueprints' => 'Blueprints & Fieldsets',
            'collections' => 'Collections',
            'entries' => 'Entries',
            'forms' => 'Forms',
            'globals' => 'Globals',
            'navs' => 'Navigations',
            'revisions' => 'Revisions',
            'taxonomies' => 'Taxonomies',
        ])->reject(function ($value, $key) {
            switch ($key) {
                case 'assets':
                    return config('statamic.eloquent-driver.asset_containers.driver') === 'eloquent'
                        || config('statamic.eloquent-driver.assets.driver') === 'eloquent';

                case 'blueprints':
                    return config('statamic.eloquent-driver.blueprints.driver') === 'eloquent';

                case 'collections':
                    return config('statamic.eloquent-driver.collections.driver') === 'eloquent'
                        || config('statamic.eloquent-driver.collection_trees.driver') === 'eloquent';

                case 'entries':
                    return config('statamic.eloquent-driver.entries.driver') === 'eloquent';

                case 'forms':
                    return config('statamic.eloquent-driver.forms.driver') === 'eloquent';

                case 'globals':
                    return config('statamic.eloquent-driver.global_sets.driver') === 'eloquent'
                        || config('statamic.eloquent-driver.global_set_variables.driver') === 'eloquent';

                case 'navs':
                    return config('statamic.eloquent-driver.navigations.driver') === 'eloquent'
                        || config('statamic.eloquent-driver.navigation_trees.driver') === 'eloquent';

                case 'revisions':
                    return config('statamic.eloquent-driver.revisions.driver') === 'eloquent';

                case 'taxonomies':
                    return config('statamic.eloquent-driver.taxonomies.driver') === 'eloquent'
                        || config('statamic.eloquent-driver.terms.driver') === 'eloquent';
            }
        });
    }

    protected function migrateAssets(): void
    {
        info('Migrating assets...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-asset-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('asset_containers');
        $this->switchToEloquentDriver('assets');

        $this->checkLine('Configured assets');

        if (confirm('Would you like to import existing assets?')) {
            $this->runArtisanCommand('statamic:eloquent:import-assets --force');
            $this->checkLine('Imported existing assets');
        }
    }

    protected function migrateBlueprints(): void
    {
        info('Migrating blueprints...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-blueprint-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('blueprints');

        $this->checkLine('Configured blueprints');

        if (confirm('Would you like to import existing blueprints?')) {
            $this->runArtisanCommand('statamic:eloquent:import-blueprints');
            $this->checkLine('Imported existing blueprints');
        }
    }

    protected function migrateCollections(): void
    {
        info('Migrating collections...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-collection-migrations');
        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-migrations');

        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('collections');
        $this->switchToEloquentDriver('collection_trees');

        $this->checkLine('Configured collections');

        if (confirm('Would you like to import existing collections?')) {
            $this->runArtisanCommand('statamic:eloquent:import-collections --force');
            $this->checkLine('Imported existing collections');
        }
    }

    protected function migrateEntries(): void
    {
        info('Migrating entries...');

        $this->switchToEloquentDriver('entries');

        if (confirm('Would you like to import existing entries?')) {
            File::put(
                config_path('statamic/eloquent-driver.php'),
                Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                    ->replace("'model'  => \Statamic\Eloquent\Entries\EntryModel::class", "'model'  => \Statamic\Eloquent\Entries\UuidEntryModel::class")
                    ->__toString()
            );

            $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-entries-table-with-string-ids');
            $this->runArtisanCommand('migrate');

            $this->runArtisanCommand('statamic:eloquent:import-entries');

            $this->checkLine('Configured & imported existing entries');

            return;
        }

        if (File::exists(base_path('content/collections/pages/home.md'))) {
            File::delete(base_path('content/collections/pages/home.md'));
        }

        if (File::exists(base_path('content/trees/collections/pages.yaml'))) {
            File::put(base_path('content/trees/collections/pages.yaml'), 'tree: {}');
        }

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-entries-table');
        $this->runArtisanCommand('migrate');

        $this->checkLine('Configured entries');
    }

    protected function migrateForms(): void
    {
        info('Migrating forms...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-form-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('forms');

        $this->checkLine('Configured forms');

        if (confirm('Would you like to import existing forms?')) {
            $this->runArtisanCommand('statamic:eloquent:import-forms');
            $this->checkLine('Imported existing forms');
        }
    }

    protected function migrateGlobals(): void
    {
        info('Migrating globals...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-global-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('global_sets');
        $this->switchToEloquentDriver('global_set_variables');

        $this->checkLine('Configured globals');

        if (confirm('Would you like to import existing globals?')) {
            $this->runArtisanCommand('statamic:eloquent:import-globals');
            $this->checkLine('Imported existing globals');
        }
    }

    protected function migrateNavs(): void
    {
        info('Migrating navs...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-migrations');

        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('navigations');
        $this->switchToEloquentDriver('navigation_trees');

        $this->checkLine('Configured navs');

        if (confirm('Would you like to import existing navs?')) {
            $this->runArtisanCommand('statamic:eloquent:import-navs --force');
            $this->checkLine('Imported existing navs');
        }
    }

    protected function migrateRevisions(): void
    {
        info('Migrating revisions...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-revision-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('revisions');

        $this->checkLine('Configured revisions');

        if (confirm('Would you like to import existing revisions?')) {
            $this->runArtisanCommand('statamic:eloquent:import-revisions');
            $this->checkLine('Imported existing revisions');
        }
    }

    protected function migrateTaxonomies(): void
    {
        info('Migrating taxonomies...');

        $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-taxonomy-migrations');
        $this->runArtisanCommand('migrate');

        $this->switchToEloquentDriver('taxonomies');
        $this->switchToEloquentDriver('terms');

        $this->checkLine('Configured taxonomies');

        if (confirm('Would you like to import existing taxonomies?')) {
            $this->runArtisanCommand('statamic:eloquent:import-taxonomies --force');
            $this->checkLine('Imported existing taxonomies');
        }
    }

    private function switchToEloquentDriver(string $repository): void
    {
        $configContents = File::get(config_path('statamic/eloquent-driver.php'));

        $configContents = Str::of($configContents)
            ->replace(
                "'{$repository}' => [\n        'driver' => 'file'",
                "'{$repository}' => [\n        'driver' => 'eloquent'"
            )
            ->__toString();

        File::put(config_path('statamic/eloquent-driver.php'), $configContents);
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
            if (Str::of($result->output())->contains('Unknown database')) {
                error('The database does not exist. Please create it before running this command.');
                exit(1);
            }

            error('Failed to run command: '.$command);
            $this->output->write($result->output());
            exit(1);
        }

        return $result;
    }
}
