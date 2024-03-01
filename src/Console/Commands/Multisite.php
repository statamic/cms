<?php

namespace Statamic\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\EnhancesCommands;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Role;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Statamic;

class Multisite extends Command
{
    use EnhancesCommands, RunsInPlease;

    protected $name = 'statamic:multisite';
    protected $description = 'Converts from a single to multisite installation';

    protected $sites;
    protected $newSiteConfigs;

    public function handle()
    {
        if (! $this->checkForAndEnablePro()) {
            return $this->crossLine('Multisite has not been enabled.');
        }

        Stache::disableUpdatingIndexes();

        $this->sites = collect(Site::default()->handle());

        $this->validateRunningOfCommand();

        $confirmed = $this->confirm("The current site handle is [<comment>{$this->siteOne()}</comment>], content will be moved into folders with this name. Is this okay?");

        if (! $confirmed) {
            $this->crossLine('Change the site handle in <comment>config/statamic/sites.php</comment> then try this command again.');

            return;
        }

        $this->info("Please enter the handles of the additional sites. Just press enter when you're done.");
        do {
            if ($site = $this->ask('Handle of site #'.($this->sites->count() + 1))) {
                $this->sites->add($site);
            }
        } while ($site !== null);

        if ($this->sites->count() < 2) {
            return $this->crossLine('Multisite has not been enabled.');
        }

        $this->clearStache();

        $config = $this->updateSiteConfig();

        $this->comment('Converting...');

        Collection::all()->each(function ($collection) {
            $this->moveCollectionContent($collection);
            $this->moveCollectionTrees($collection);
            $this->updateCollection($collection);
            $this->checkLine("Collection [<comment>{$collection->handle()}</comment>] updated.");
        });

        GlobalSet::all()->each(function ($set) {
            $this->moveGlobalSet($set);
            $this->checkLine("Global [<comment>{$set->handle()}</comment>] updated.");
        });

        Nav::all()->each(function ($nav) {
            $this->moveNav($nav);
            $this->checkLine("Nav [<comment>{$nav->handle()}</comment>] updated.");
        });

        $this->addPermissions();

        Cache::clear();
        $this->checkLine('Cache cleared.');

        $this->checkInfo('Done!');
    }

    protected function clearStache()
    {
        Stache::clear();
        $this->checkLine('Stache cleared.');
        $this->line('');
    }

    protected function updateSiteConfig()
    {
        $this->newSiteConfigs = $this->newSites()->mapWithKeys(function ($site) {
            return [$site => [
                'name' => $site,
                'locale' => 'en_US',
                'url' => "/{$site}/",
            ]];
        });

        // TODO: Make sure we're doing correct merge behaviour here...
        $sites = Site::toArray() + $this->newSiteConfigs->all();

        Site::setSites($sites)->save();

        Stache::sites(Site::all()->map->handle());

        return $sites;
    }

    protected function moveCollectionContent($collection)
    {
        $handle = $collection->handle();
        $base = "content/collections/{$handle}";

        File::makeDirectory("{$base}/{$this->siteOne()}");

        File::getFiles($base)->each(function ($file) use ($base) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            File::move($file, "{$base}/{$this->siteOne()}/{$filename}");
        });
    }

    protected function updateCollection($collection)
    {
        $collection
            ->sites($this->sites->all())
            ->save();
    }

    protected function moveCollectionTrees($collection)
    {
        if (! $collection->structure()) {
            return;
        }

        $collection->structure()->trees()->each->save();
    }

    protected function moveGlobalSet($set)
    {
        $yaml = YAML::file($set->path())->parse();
        $data = $yaml['data'] ?? [];

        $set->addLocalization($origin = $set->makeLocalization($this->siteOne())->data($data));

        $this->newSites()->each(function ($site) use ($set, $origin) {
            $set->addLocalization($set->makeLocalization($site)->origin($origin));
        });

        $set->save();
    }

    protected function moveNav($nav)
    {
        $default = $nav->trees()->first();

        $default->save();

        $this->sites->each(function ($site) use ($nav, $default) {
            $nav->makeTree($site, $default->tree())->save();
        });
    }

    protected function validateRunningOfCommand()
    {
        if (Site::hasMultiple()) {
            exit($this->error('Already configured for multi-site.'));
        }

        if ($this->commandMayHaveBeenRan()) {
            exit($this->error('Command may have already been run. Did you update your config/statamic/sites.php file?'));
        }
    }

    protected function commandMayHaveBeenRan()
    {
        return $this->collectionsHaveBeenMoved()
            || $this->globalsHaveBeenMoved()
            || $this->navsHaveBeenMoved();
    }

    protected function collectionsHaveBeenMoved()
    {
        if (! $collection = Collection::all()->first()) {
            return false;
        }

        return File::isDirectory("content/collections/{$collection->handle()}/{$this->siteOne()}");
    }

    protected function globalsHaveBeenMoved()
    {
        return File::isDirectory("content/globals/{$this->siteOne()}");
    }

    protected function navsHaveBeenMoved()
    {
        return File::isDirectory("content/navigation/{$this->siteOne()}");
    }

    protected function checkForAndEnablePro()
    {
        if (Statamic::pro()) {
            return true;
        }

        if (! $this->confirm('Statamic Pro is required for multiple sites. Would you like to enable it?', true)) {
            return false;
        }

        try {
            Statamic::enablePro();
            $this->checkLine('Statamic Pro has been enabled.');
        } catch (\Exception $e) {
            $this->error('Could not automatically enable Pro.');
            $this->line('You can enable it manually in <comment>config/statamic/editions.php</comment>');

            return false;
        }

        return true;
    }

    protected function siteOne()
    {
        return $this->sites->first();
    }

    protected function newSites()
    {
        return $this->sites->slice(1);
    }

    protected function addPermissions()
    {
        Role::all()->each(function ($role) {
            Site::all()->each(fn ($site) => $role->addPermission("access {$site->handle()} site"));
            $role->save();
            $this->checkLine("Site permissions added to [<comment>{$role->handle()}</comment>] role.");
        });
    }
}
