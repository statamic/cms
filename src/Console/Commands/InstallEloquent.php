<?php

namespace Statamic\Console\Commands;

use Statamic\Facades\File;
use Statamic\Facades\Blink;
use Illuminate\Console\Command;
use function Laravel\Prompts\spin;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Statamic\Console\RunsInPlease;
use Statamic\Console\EnhancesCommands;
use function Laravel\Prompts\multiselect;
use Facades\Statamic\Console\Processes\Composer;
use Statamic\Console\Commands\Concerns\RunsArtisanCommand;
use Statamic\Console\Commands\Eloquent\InstallEloquentEntries;
use Statamic\Console\Commands\Eloquent\InstallEloquentRepository;
use Statamic\Console\Commands\Eloquent\InstallEloquentCollections;
use Statamic\Console\Commands\Eloquent\InstallEloquentCollectionTrees;

class InstallEloquent extends Command
{
    use EnhancesCommands, RunsInPlease, RunsArtisanCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:install:eloquent
        { --all : Configures all repositories to use the database }
        { --repositories= : Comma separated list of repositories to migrate }
        { --import : Whether existing data should be imported }
        { --without-messages : Disables output messages }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Install & configure Statamic's Eloquent Driver package";

    /**
     * The default repositories of the installer.
     */
    protected static array $repositories = [
        InstallEloquentCollections::class,
        InstallEloquentCollectionTrees::class,
        InstallEloquentEntries::class,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->installEloquentDriver();
        $this->setupDatabase();
        $this->migrateRepositories();
    }

    protected function installEloquentDriver()
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
    }

    protected function setupDatabase()
    {
        try {
            DB::connection()->getPDO();
            DB::connection()->getDatabaseName();
        } catch (\PDOException $e) {
            $this->components->error('Failed to connect to the configured database. Please check your database configuration and try again.');
            exit(1);
        }
    }

    protected function migrateRepositories()
    {
        if ($this->repositories()->reject(fn ($repository) => $repository->hasBeenMigrated())->isEmpty()) {
            $this->components->warn("No repositories left to migrate. You're already using the Eloquent Driver for all repositories.");
            exit(1);
        }

        $this->repositories()
            ->only($this->selectedRepositories())
            ->each(function ($repository) {
                if ($repository->hasBeenMigrated()) {
                    return $this->components->warn("Skipping. The {$repository->repoTitle()} repository is already using the Eloquent Driver.");
                }

                // Pass valid options from this command down to the individual repository command
                $commandOptions = collect($this->options())
                    ->intersectByKeys($repository->getDefinition()->getOptions())
                    ->mapWithKeys(fn ($value, $key) => ["--{$key}" => $value])
                    ->all();

                $this->call($repository->getName(), $commandOptions);
            });
    }

    protected function selectedRepositories(): array
    {
        if ($this->option('all')) {
            return $this->repositories()->map->repoHandle()->all();
        }

        if ($repositories = $this->option('repositories')) {
            $repositories = collect(explode(',', $repositories))
                ->map(fn ($repo) => trim(strtolower($repo)))
                ->unique();

            $invalidRepositories = $repositories->reject(fn ($repo) => $this->repositories()->has($repo));

            if ($invalidRepositories->isNotEmpty()) {
                $this->components->warn("Some of the repositories you provided are invalid: {$invalidRepositories->implode(', ')}");
            }

            return $repositories
                ->filter(fn ($repository) => ! $invalidRepositories->contains($repository))
                ->values()
                ->all();
        }

        return multiselect(
            label: 'Which repositories would you like to migrate?',
            options: $this->repositories()
                ->reject(fn ($repository) => $repository->hasBeenMigrated())
                ->map->repoTitle(),
            validate: fn (array $values) => count($values) === 0
                ? 'You must select at least one repository to migrate.'
                : null,
            hint: 'You can always import other repositories later.'
        );
    }

    public static function register(string $repository): void
    {
        self::$repositories[] = $repository;
    }

    protected function repositories(): Collection
    {
        return Blink::once('install-eloquent-driver-repositories', function () {
            return collect(self::$repositories)
                ->sort()
                ->map(fn (string $repo) => app($repo))
                ->mapWithKeys(fn (InstallEloquentRepository $repo) => [$repo->repoHandle() => $repo]);
        });
    }

    private function infoMessage(string $message): void
    {
        if ($this->option('without-messages')) {
            return;
        }

        $this->components->info($message);
    }
}
