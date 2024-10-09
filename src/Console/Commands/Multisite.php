<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\Collection;
use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Rules\Handle;
use Statamic\Statamic;
use Wilderborn\Partyline\Facade as Partyline;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class Multisite extends Command
{
    use ConfirmableTrait, EnhancesCommands, RunsInPlease, ValidatesInput;

    protected $signature = 'statamic:multisite';

    protected $description = 'Converts from a single to multisite installation';

    private $siteHandle;

    public function handle()
    {
        $okayToConvert = $this->confirmToProceed()
            && $this->isFreshRun()
            && $this->promptForSiteHandle()
            && $this->ensureProIsEnabled()
            && $this->ensureMultisiteIsEnabled();

        if (! $okayToConvert) {
            return;
        }

        $this
            ->disablePartyline()
            ->updateSiteConfig()
            ->clearStache()
            ->convertCollections()
            ->convertGlobalSets()
            ->convertNavs()
            ->addPermissions()
            ->clearCache();

        $this->components->info('Successfully converted from single to multisite installation!');

        $this->line('You may now manage your sites in the Control Panel at [/cp/sites].');
    }

    private function isFreshRun(): bool
    {
        if (Site::multiEnabled() && Site::hasMultiple()) {
            $this->components->error('Already configured for multi-site.');

            return false;
        }

        $siteHandle = Site::default()->handle();

        if (Site::multiEnabled() && $this->commandMayHaveBeenRan($siteHandle)) {
            $this->components->error("Command may have already been run. Site directories for site [{$siteHandle}] already exist!");

            return false;
        }

        return true;
    }

    private function commandMayHaveBeenRan(string $siteHandle): bool
    {
        return $this->collectionsHaveBeenMoved($siteHandle)
            || $this->globalsHaveBeenMoved($siteHandle)
            || $this->navsHaveBeenMoved($siteHandle);
    }

    private function collectionsHaveBeenMoved(string $siteHandle): bool
    {
        if (! $collection = Collection::all()->first()) {
            return false;
        }

        $directory = Config::get('statamic.stache.stores.entries.directory').DIRECTORY_SEPARATOR.$collection->handle().DIRECTORY_SEPARATOR.$siteHandle;

        return File::isDirectory($directory);
    }

    private function globalsHaveBeenMoved(string $siteHandle): bool
    {
        $directory = Config::get('statamic.stache.stores.globals.directory').DIRECTORY_SEPARATOR.$siteHandle;

        return File::isDirectory($directory);
    }

    private function navsHaveBeenMoved(string $siteHandle): bool
    {
        $directory = Config::get('statamic.stache.stores.navigation.directory').DIRECTORY_SEPARATOR.$siteHandle;

        return File::isDirectory($directory);
    }

    private function promptForSiteHandle(): bool
    {
        $this->siteHandle = text(label: 'Please enter a site handle (default site content will be moved into folders with this name)', default: Site::default()->handle());

        if ($this->validationFails($this->siteHandle, ['required', new Handle])) {
            return $this->promptForSiteHandle();
        }

        return true;
    }

    private function ensureProIsEnabled(): bool
    {
        if (Statamic::pro()) {
            return true;
        }

        if (! confirm('Statamic Pro is required for multiple sites. Enable pro and continue?', true)) {
            return false;
        }

        Statamic::enablePro();

        if (! Statamic::pro()) {
            $this->components->error('Could not reliably enable pro, please modify your [config/statamic/editions.php] as follows:');
            $this->line("'pro' => env('STATAMIC_PRO_ENABLED', false)");

            return false;
        }

        $this->components->info('Statamic Pro enabled successfully.');

        return true;
    }

    private function ensureMultisiteIsEnabled(): bool
    {
        $contents = File::get($configPath = config_path('statamic/system.php'));

        if (str_contains($contents, "'multisite' => true,")) {
            return true;
        } elseif (str_contains($contents, "'multisite' => false,")) {
            $contents = str_replace("'multisite' => false,", "'multisite' => true,", $contents);
        } else {
            $this->components->error('Could not reliably enable multisite, please modify your [config/statamic/system.php] as follows:');
            $this->line("'multisite' => true,");

            return false;
        }

        File::put($configPath, $contents);

        $this->components->info('Multisite enabled.');

        return true;
    }

    private function disablePartyline(): self
    {
        $dummyClass = new class
        {
            public function __call($method, $args)
            {
                //
            }
        };

        Partyline::swap($dummyClass);

        return $this;
    }

    private function updateSiteConfig(): self
    {
        $siteConfig = collect(Site::config())->first();

        $this->components->task(
            description: 'Updating site config...',
            task: function () use ($siteConfig) {
                Site::setSites([$this->siteHandle => $siteConfig])->save();
            }
        );

        return $this;
    }

    private function clearStache(): self
    {
        Config::set('statamic.system.multisite', false);

        $this->components->task(
            description: 'Clearing Stache...',
            task: function () {
                Stache::disableUpdatingIndexes();
                Stache::clear();
            }
        );

        return $this;
    }

    private function convertCollections(): self
    {
        Collection::all()->each(function ($collection) {
            $this->components->task(
                description: "Updating collection [{$collection->handle()}]...",
                task: function () use ($collection) {
                    $this->moveCollectionContent($collection);
                    $this->moveCollectionTrees($collection);
                    $this->updateCollection($collection);
                }
            );
        });

        return $this;
    }

    private function moveCollectionContent($collection): void
    {
        Config::set('statamic.system.multisite', false);

        $base = Config::get('statamic.stache.stores.entries.directory').DIRECTORY_SEPARATOR.$collection->handle();

        File::makeDirectory("{$base}/{$this->siteHandle}");

        File::getFiles($base)->each(function ($file) use ($base) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            File::move($file, "{$base}/{$this->siteHandle}/{$filename}");
        });
    }

    private function updateCollection($collection): void
    {
        $collection
            ->sites([$this->siteHandle])
            ->save();
    }

    private function moveCollectionTrees($collection): void
    {
        if (! $collection->structure()) {
            return;
        }

        $collection->structure()->trees()->each->save();
    }

    private function convertGlobalSets(): self
    {
        Config::set('statamic.system.multisite', true);

        GlobalSet::all()->each(function ($set) {
            $this->components->task(
                description: "Updating global [{$set->handle()}]...",
                task: function () use ($set) {
                    $this->moveGlobalSet($set);
                }
            );
        });

        return $this;
    }

    private function moveGlobalSet($set): void
    {
        $yaml = YAML::file($set->path())->parse();

        $data = $yaml['data'] ?? [];

        $set->addLocalization($set->makeLocalization($this->siteHandle)->data($data));

        $set->save();
    }

    private function convertNavs(): self
    {
        Config::set('statamic.system.multisite', true);

        Nav::all()->each(function ($nav) {
            $this->components->task(
                description: "Updating nav [{$nav->handle()}]...",
                task: function () use ($nav) {
                    $this->moveNav($nav);
                }
            );
        });

        return $this;
    }

    private function moveNav($nav): void
    {
        $default = $nav->trees()->first();

        $default->save();

        $nav->makeTree($this->siteHandle, $default->tree())->save();
    }

    private function addPermissions(): self
    {
        Role::all()->each(function ($role) {
            $this->components->task(
                description: "Adding site permissions to [{$role->handle()}] role...",
                task: function () use ($role) {
                    $role->addPermission("access {$this->siteHandle} site");
                    $role->save();
                }
            );
        });

        return $this;
    }

    private function clearCache(): self
    {
        $this->components->task(
            description: 'Clearing cache...',
            task: function () {
                Cache::clear();
            }
        );

        return $this;
    }
}
