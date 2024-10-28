<?php

namespace Statamic\Console\Commands;

use Facades\Statamic\Console\Processes\Composer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\File;
use Statamic\Support\Str;
use Symfony\Component\Process\PhpExecutableFinder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;

class InstallEloquentDriver extends Command
{
    use EnhancesCommands, RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install:eloquent-driver
        { --all : Configures all repositories to use the database }
        { --import : Whether existing data should be imported }
        { --without-messages : Disables output messages }';

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
            spin(
                callback: fn () => Composer::withoutQueue()->throwOnFailure()->require('statamic/eloquent-driver'),
                message: 'Installing the statamic/eloquent-driver package...'
            );

            $this->infoMessage('Installed statamic/eloquent-driver package');
        }

        if (! File::exists(config_path('statamic/eloquent-driver.php'))) {
            $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-config');
            $this->infoMessage('Config file [config/statamic/eloquent-driver.php] published successfully.');
        }

        try {
            DB::connection()->getPDO();
            DB::connection()->getDatabaseName();
        } catch (\PDOException $e) {
            return $this->components->error('Failed to connect to the configured database. Please check your database configuration and try again.');
        }

        if ($this->availableRepositories()->isEmpty()) {
            return $this->components->warn("No repositories left to migrate. You're already using the Eloquent Driver for all repositories.");
        }

        $repositories = $this->repositories();

        foreach ($repositories as $repository) {
            $method = 'migrate'.Str::studly($repository);
            $this->$method();
        }
    }

    protected function repositories(): array
    {
        if ($this->option('all')) {
            return $this->availableRepositories()->keys()->all();
        }

        return multiselect(
            label: 'Which repositories would you like to migrate?',
            options: $this->availableRepositories()->all(),
            validate: fn (array $values) => count($values) === 0
                ? 'You must select at least one repository to migrate.'
                : null,
            hint: 'You can always import other repositories later.'
        );
    }

    protected function availableRepositories(): Collection
    {
        return collect([
            'asset_containers' => 'Asset Containers',
            'assets' => 'Assets',
            'blueprints' => 'Blueprints',
            'collections' => 'Collections',
            'collection_trees' => 'Collection Trees',
            'entries' => 'Entries',
            'fieldsets' => 'Fieldsets',
            'forms' => 'Forms',
            'form_submissions' => 'Form Submissions',
            'globals' => 'Globals',
            'global_variables' => 'Global Variables',
            'navs' => 'Navigations',
            'nav_trees' => 'Navigation Trees',
            'revisions' => 'Revisions',
            'sites' => 'Sites',
            'taxonomies' => 'Taxonomies',
            'terms' => 'Terms',
            'tokens' => 'Tokens',
        ])->reject(function ($value, $key) {
            switch ($key) {
                case 'asset_containers':
                    return config('statamic.eloquent-driver.asset_containers.driver') === 'eloquent';

                case 'assets':
                    return config('statamic.eloquent-driver.assets.driver') === 'eloquent';

                case 'blueprints':
                    return config('statamic.eloquent-driver.blueprints.driver') === 'eloquent';

                case 'collections':
                    return config('statamic.eloquent-driver.collections.driver') === 'eloquent';

                case 'collection_trees':
                    return config('statamic.eloquent-driver.collection_trees.driver') === 'eloquent';

                case 'entries':
                    return config('statamic.eloquent-driver.entries.driver') === 'eloquent';

                case 'fieldsets':
                    return config('statamic.eloquent-driver.fieldsets.driver') === 'eloquent';

                case 'forms':
                    return config('statamic.eloquent-driver.forms.driver') === 'eloquent';

                case 'form_submissions':
                    return config('statamic.eloquent-driver.form_submissions.driver') === 'eloquent';

                case 'globals':
                    return config('statamic.eloquent-driver.global_sets.driver') === 'eloquent';

                case 'global_variables':
                    return config('statamic.eloquent-driver.global_set_variables.driver') === 'eloquent';

                case 'navs':
                    return config('statamic.eloquent-driver.navigations.driver') === 'eloquent';

                case 'nav_trees':
                    return config('statamic.eloquent-driver.navigation_trees.driver') === 'eloquent';

                case 'revisions':
                    return ! config('statamic.revisions.enabled')
                        || config('statamic.eloquent-driver.revisions.driver') === 'eloquent';

                case 'sites':
                    return config('statamic.eloquent-driver.sites.driver') === 'eloquent';

                case 'taxonomies':
                    return config('statamic.eloquent-driver.taxonomies.driver') === 'eloquent';

                case 'terms':
                    return config('statamic.eloquent-driver.terms.driver') === 'eloquent';

                case 'tokens':
                    return config('statamic.eloquent-driver.tokens.driver') === 'eloquent';
            }
        });
    }

    protected function migrateAssetContainers(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-asset-container-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('asset_containers');
            },
            message: 'Migrating asset containers...'
        );

        $this->infoMessage('Configured asset containers');

        if ($this->shouldImport('asset containers')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-assets --force --only-asset-containers'),
                message: 'Importing existing asset containers...'
            );

            $this->infoMessage('Imported existing asset containers');
        }
    }

    protected function migrateAssets(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-asset-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('assets');
            },
            message: 'Migrating assets...'
        );

        $this->infoMessage('Configured assets');

        if ($this->shouldImport('assets')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-assets --force --only-assets'),
                message: 'Importing existing assets...'
            );

            $this->infoMessage('Imported existing assets');
        }
    }

    protected function migrateBlueprints(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-blueprint-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('blueprints');
            },
            message: 'Migrating blueprints...'
        );

        $this->infoMessage('Configured blueprints');

        if ($this->shouldImport('blueprints')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-blueprints --force --only-blueprints'),
                message: 'Importing existing blueprints...'
            );

            $this->infoMessage('Imported existing blueprints');
        }
    }

    protected function migrateCollections(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-collection-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('collections');
            },
            message: 'Migrating collections...'
        );

        $this->infoMessage('Configured collections');

        if ($this->shouldImport('collections')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-collections --force --only-collections'),
                message: 'Importing existing collections...'
            );

            $this->infoMessage('Imported existing collections');
        }
    }

    protected function migrateCollectionTrees(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-tree-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('collection_trees');
            },
            message: 'Migrating collection trees...'
        );

        $this->infoMessage('Configured collection trees');

        if ($this->shouldImport('collection trees')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-collections --force --only-collection-trees'),
                message: 'Importing existing collections...'
            );

            $this->infoMessage('Imported existing collection trees');
        }
    }

    protected function migrateEntries(): void
    {
        $shouldImportEntries = $this->shouldImport('entries');

        spin(
            callback: function () use ($shouldImportEntries) {
                $this->switchToEloquentDriver('entries');

                if ($shouldImportEntries) {
                    File::put(
                        config_path('statamic/eloquent-driver.php'),
                        Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                            ->replace("'model' => \Statamic\Eloquent\Entries\EntryModel::class", "'model' => \Statamic\Eloquent\Entries\UuidEntryModel::class")
                            ->__toString()
                    );

                    $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-entries-table-with-string-ids');
                    $this->runArtisanCommand('migrate');

                    $this->runArtisanCommand('statamic:eloquent:import-entries');

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
            },
            message: $shouldImportEntries
                ? 'Migrating entries...'
                : 'Migrating and importing entries...'
        );

        $this->infoMessage(
            $shouldImportEntries
                ? 'Configured & imported existing entries'
                : 'Configured entries'
        );
    }

    protected function migrateFieldsets(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-fieldset-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('fieldsets');
            },
            message: 'Migrating fieldsets...'
        );

        $this->infoMessage('Configured fieldsets');

        if ($this->shouldImport('fieldsets')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-blueprints --force --only-fieldsets'),
                message: 'Importing existing fieldsets...'
            );

            $this->infoMessage('Imported existing fieldsets');
        }
    }

    protected function migrateForms(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-form-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('forms');
            },
            message: 'Migrating forms...'
        );

        $this->infoMessage('Configured forms');

        if ($this->shouldImport('forms')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-forms --only-forms'),
                message: 'Importing existing forms...'
            );

            $this->infoMessage('Imported existing forms');
        }
    }

    protected function migrateFormSubmissions(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-form-submission-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('form_submissions');
            },
            message: 'Migrating form submissions...'
        );

        $this->infoMessage('Configured form submissions');

        if ($this->shouldImport('form submissions')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-forms --only-form-submissions'),
                message: 'Importing existing form submissions...'
            );

            $this->infoMessage('Imported existing form submissions');
        }
    }

    protected function migrateGlobals(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-global-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('global_sets');
            },
            message: 'Migrating globals...'
        );

        $this->infoMessage('Configured globals');

        if ($this->shouldImport('globals')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-globals --only-global-sets'),
                message: 'Importing existing globals...'
            );

            $this->infoMessage('Imported existing globals');
        }
    }

    protected function migrateGlobalVariables(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-global-variables-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('global_set_variables');
            },
            message: 'Migrating global variables...'
        );

        $this->infoMessage('Configured global variables');

        if ($this->shouldImport('global variables')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-globals --only-global-variables'),
                message: 'Importing existing global variables...'
            );

            $this->infoMessage('Imported existing global variables');
        }
    }

    protected function migrateNavs(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('navigations');
            },
            message: 'Migrating navs...'
        );

        $this->infoMessage('Configured navs');

        if ($this->shouldImport('navs')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-navs --force --only-navs'),
                message: 'Importing existing navs...'
            );

            $this->infoMessage('Imported existing navs');
        }
    }

    protected function migrateNavTrees(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-navigation-tree-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('navigation_trees');
            },
            message: 'Migrating nav trees...'
        );

        $this->infoMessage('Configured nav trees');

        if ($this->shouldImport('nav trees')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-navs --force --only-nav-trees'),
                message: 'Importing existing nav trees...'
            );

            $this->infoMessage('Imported existing navs trees');
        }
    }

    protected function migrateRevisions(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-revision-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('revisions');
            },
        );

        $this->infoMessage('Configured revisions');

        if ($this->shouldImport('revisions')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-revisions'),
                message: 'Importing existing revisions...'
            );

            $this->infoMessage('Imported existing revisions');
        }
    }

    protected function migrateSites(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-site-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('sites');

                $this->runArtisanCommand('statamic:eloquent:import-sites');
            },
            message: 'Migrating sites...'
        );

        $this->infoMessage('Configured & imported sites');
    }

    protected function migrateTaxonomies(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-taxonomy-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('taxonomies');
            },
            message: 'Migrating taxonomies...'
        );

        $this->infoMessage('Configured taxonomies');

        if ($this->shouldImport('taxonomies')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-taxonomies --force --only-taxonomies'),
                message: 'Importing existing taxonomies...'
            );

            $this->infoMessage('Imported existing taxonomies');
        }
    }

    protected function migrateTerms(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-term-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('terms');
            },
            message: 'Migrating terms...'
        );

        $this->infoMessage('Configured terms');

        if ($this->shouldImport('terms')) {
            spin(
                callback: fn () => $this->runArtisanCommand('statamic:eloquent:import-taxonomies --force --only-terms'),
                message: 'Importing existing terms...'
            );

            $this->infoMessage('Imported existing terms');
        }
    }

    protected function migrateTokens(): void
    {
        spin(
            callback: function () {
                $this->runArtisanCommand('vendor:publish --tag=statamic-eloquent-token-migrations');
                $this->runArtisanCommand('migrate');

                $this->switchToEloquentDriver('tokens');
            },
            message: 'Migrating tokens...'
        );

        $this->infoMessage('Configured tokens');
    }

    private function shouldImport(string $repository): bool
    {
        return $this->option('import') || confirm("Would you like to import existing {$repository}?");
    }

    private function infoMessage(string $message): void
    {
        if ($this->option('without-messages')) {
            return;
        }

        $this->components->info('Configured asset containers');
    }

    private function switchToEloquentDriver(string $repository): void
    {
        File::put(
            config_path('statamic/eloquent-driver.php'),
            Str::of(File::get(config_path('statamic/eloquent-driver.php')))
                ->replace(
                    "'{$repository}' => [\n        'driver' => 'file'",
                    "'{$repository}' => [\n        'driver' => 'eloquent'"
                )
                ->__toString()
        );
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

        $result = Process::forever()->run($components, function ($type, $line) use ($writeOutput) {
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
