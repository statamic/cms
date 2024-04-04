<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Console\ValidatesInput;
use Statamic\Facades\Collection;
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
            && $this->confirmSiteHandle()
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
            ->clearCache()
            ->checkInfo('Successfully converted from single to multisite installation!');
    }

    private function isFreshRun(): bool
    {
        if (Site::hasMultiple()) {
            $this->error('Already configured for multi-site.');

            return false;
        }

        return true;
    }

    private function confirmSiteHandle(): bool
    {
        $this->siteHandle ??= Site::default()->handle();

        if (! $this->confirm("Content will be moved into site folders by the name [<comment>{$this->siteHandle}</comment>]. Is this okay?", true)) {
            $this
                ->promptForNewSiteHandle()
                ->confirmSiteHandle();
        }

        return true;
    }

    private function promptForNewSiteHandle(): self
    {
        $this->siteHandle = $this->ask('Please enter a new site handle');

        if ($this->validationFails($this->siteHandle, ['required', new Handle])) {
            return $this->promptForNewSiteHandle();
        }

        return $this;
    }

    private function ensureProIsEnabled(): bool
    {
        if (Statamic::pro()) {
            return true;
        }

        if (! $this->confirm('Statamic Pro is required for multiple sites. Enable pro and continue?', true)) {
            return false;
        }

        Statamic::enablePro();

        if (! Statamic::pro()) {
            $this->error('Could not reliably enable pro, please modify your [config/statamic/editions.php] as follows:');
            $this->line("'pro' => env('STATAMIC_PRO_ENABLED', false)");

            return false;
        }

        $this->checkLine('Statamic Pro enabled.');

        return true;
    }

    private function ensureMultisiteIsEnabled(): bool
    {
        $contents = File::get($configPath = config_path('statamic/sites.php'));

        if (str_contains($contents, "'enabled' => true,")) {
            return true;
        } elseif (str_contains($contents, "'enabled' => false,")) {
            $contents = str_replace("'enabled' => false,", "'enabled' => true,", $contents);
        } else {
            $this->error('Could not reliably enable multisite, please modify your [config/statamic/sites.php] as follows:');
            $this->line("'enabled' => true,");

            return false;
        }

        File::put($configPath, $contents);

        $this->checkLine('Multisite enabled.');

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

        Site::setSites([$this->siteHandle => $siteConfig])->save();

        $this->checkLine('Site config updated.');

        return $this;
    }

    private function clearStache(): self
    {
        Stache::disableUpdatingIndexes();
        Stache::clear();

        $this->checkLine('Stache cleared.');

        return $this;
    }

    private function convertCollections(): self
    {
        Collection::all()->each(function ($collection) {
            $this->moveCollectionContent($collection);
            $this->moveCollectionTrees($collection);
            $this->updateCollection($collection);
            $this->checkLine("Collection [<comment>{$collection->handle()}</comment>] updated.");
        });

        return $this;
    }

    private function moveCollectionContent($collection): void
    {
        $handle = $collection->handle();

        $base = "content/collections/{$handle}";

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
        GlobalSet::all()->each(function ($set) {
            $this->moveGlobalSet($set);
            $this->checkLine("Global [<comment>{$set->handle()}</comment>] updated.");
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
        Nav::all()->each(function ($nav) {
            $this->moveNav($nav);
            $this->checkLine("Nav [<comment>{$nav->handle()}</comment>] updated.");
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
            $role->addPermission("access {$this->siteHandle} site");
            $role->save();
            $this->checkLine("Site permissions added to [<comment>{$role->handle()}</comment>] role.");
        });

        return $this;
    }

    private function clearCache(): self
    {
        Cache::clear();

        $this->checkLine('Cache cleared.');

        return $this;
    }
}
